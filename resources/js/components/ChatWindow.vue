<template>
  <div class="flex-1 flex flex-col">
    <div class="p-4 bg-gray-800 text-white flex items-center justify-between">
      <div>
        <h2 class="text-xl font-bold">{{ friend.name ?? 'Chat' }}</h2>
        <span class="text-xs">{{ isOnline }}</span>
      </div>
      <button class="text-gray-400 hover:text-white" @click="doLogout">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 12h12M6 12l6 6m-6-6l6-6" />
        </svg>
      </button>
    </div>
    <div id="auto-scroll" class="flex-1 auto-scroll overflow-y-auto p-4 bg-gray-100 space-y-4" v-if="messagesComputed">
      <div v-for="message in messagesComputed" :key="message.id" :class="messageClass(message)">
        <p class="text-sm">{{ message.text }}</p>
        <p class="text-xs text-gray-400">{{ formatTimestamp(message.created_at) }}</p>
      </div>
    </div>
    <div class="p-4 bg-gray-200 flex items-center sticky top-[100vh]">
      <input v-model="newMessage" @keyup.enter="sendMessage" :disabled="!friend.id"
        class="flex-1 p-2 rounded-md border border-gray-300 focus:outline-none focus:ring focus:ring-blue-500"
        placeholder="Type a message...">
      <button @click="sendMessage"
        class="ml-2 px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">Send</button>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import Echo from 'laravel-echo';
import { useUserStore } from "../stores/user";
import { useRoute, useRouter } from 'vue-router'

const router = useRouter()
const userStore = useUserStore();
const props = defineProps(['messages', 'friend', 'room', 'fetchFriends']);
const newMessage = ref('');
const isOnline = ref('');
const roomId = computed(() => {
  if(props.room) {
    return props.room;
  } else {
    return Math.random().toString(36).slice(2);
  }
})

const selectedUserId = computed(() => {
  return props.friend.id;
})

watch(props, async (newprops, oldprops) => {
  console.log('props => ', newprops);
});

const listenToRoom = (roomId) => {
  console.log('listenToRoom roomId => ', roomId);
  const echo = new Echo({
    broadcaster: 'pusher',
    key: '42b422186b913915b461',
    authEndpoint: '/broadcasting/auth',
    cluster: 'ap2',
    encrypted: true,
    auth: {
      headers: {
        'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]').content,
        Authorization: `Bearer ${userStore.token}`
      },
    },
  });
  echo.join(`chat-room.${roomId}`)
  .here((users) => {
    isOnline.value = 'Offline';
    users.forEach((user) => {
      if(user.id == selectedUserId.value) {
        isOnline.value = 'Online';
        return false;
      }
    })
  })
  .joining((user) => {
    if(user)
      isOnline.value = 'Online'
  })
  .leaving((user) => {
    if(user)
      isOnline.value = 'Offline'
  })
  .listen('.chat-room', (e) => {
    props.messages.push(e.message);
    var height = document.getElementById('auto-scroll').offsetHeight;
    document.getElementById('auto-scroll').scrollTo('', (height*3));
  });
};


onMounted(() => {
  listenPrivateChannel();
})

const listenPrivateChannel = () => {
  const echo = new Echo({
    broadcaster: 'pusher',
    key: '42b422186b913915b461',
    authEndpoint: '/broadcasting/auth',
    cluster: 'ap2',
    encrypted: true,
    auth: {
      headers: {
        'X-CSRF-TOKEN': document.head.querySelector('meta[name="csrf-token"]').content,
        Authorization: `Bearer ${userStore.token}`
      },
    },
  });
  echo.private(`new-friend.${userStore.user.id}`)
  .listen('.new-friend', (e) => {
    // console.log('private e => ', e);
    if(e.firstMsg) {
      props.fetchFriends();
    }
    // props.messages.push(e.message);
  });
};

watch(roomId, async (newRoomId, oldRoomId=null) => {
  if (newRoomId) {
    listenToRoom(roomId.value);
  }
});

const sendMessage = async () => {
  if (newMessage.value.trim() !== '') {
    await fetch('http://127.0.0.1:8000/api/messages', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${userStore.token}`
      },
      body: JSON.stringify({ message: newMessage.value, 'receiver_id': selectedUserId.value, room_id: roomId.value }),
    }).then((response) => {
      return response.json();
    }).then((data) => {
      if(data.first_msg) {
        props.messages.push(data.message);
        listenToRoom(data.message.room_id);
      }
      newMessage.value = ''; 
    });
    props.fetchFriends();
  }
};

const messagesComputed = computed(() => {
  return props.messages;
})

const messageClass = (message) => {
  return message.sender_id !== userStore.user.id
    ? 'self-end bg-blue-500 text-white p-3 rounded-lg shadow-md'
    : 'self-start bg-gray-300 text-black p-3 rounded-lg shadow-md';
};

const formatTimestamp = (timestamp) => {
  const date = new Date(timestamp);
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  const hours = String(date.getHours()).padStart(2, '0');
  const minutes = String(date.getMinutes()).padStart(2, '0');

  return `${year}-${month}-${day} ${hours}:${minutes}`;
}

const doLogout = () => {
  let result = userStore.logout();
  if(result) {
      router.push({ name: 'login' });
  }
}
</script>
