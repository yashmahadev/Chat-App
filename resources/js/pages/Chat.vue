<template>
    <div class="flex h-screen">
        <Sidebar :friends="friends" @openUserChatWindow="handleOpenUserChatWindow" />
        <ChatWindow :messages="messages" :friend="friend" :room="room" :fetchFriends="fetchFriends" />
    </div>
</template>
<script setup>
import Sidebar from '../components/Sidebar.vue'
import ChatWindow from '../components/ChatWindow.vue'
import { ref, onMounted } from 'vue';
import { useUserStore } from "../stores/user";

const messages = ref([]);
const friends = ref([]);
const friend = ref({});
const room = ref({});
const userStore = useUserStore();

onMounted(() => {
    fetchFriends();
});

const fetchFriends = async() => {
    console.log('call fetchFriends');
    await fetch('http://127.0.0.1:8000/api/friends', {
        headers: {
        'Authorization': `Bearer ${userStore.token}`
        }
    })
    .then(response => response.json())
    .then(data => {
        friends.value = data.friends;
    });
}

const friendFetchMsg = (friendId) => {
    fetch('http://127.0.0.1:8000/api/get-messages', {
        method: 'POST',
        headers: {
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${userStore.token}`
        },
        body: JSON.stringify({ 'friend_id': friendId }),
    })
    .then(response => response.json())
    .then(data => {
        messages.value = data.messages;
        friend.value = data.friend;
        room.value = data.room;
    });
}

const handleOpenUserChatWindow = (friendId) => {
    friendFetchMsg(friendId);
}

</script>