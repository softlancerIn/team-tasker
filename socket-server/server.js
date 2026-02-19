import express from 'express';
import { createServer } from 'http';
import { Server } from 'socket.io';

const app = express();
const server = createServer(app);
const io = new Server(server, {
    cors: {
        origin: '*', // Allow all origins for simplicity in dev
        methods: ['GET', 'POST']
    }
});

const onlineUsers = new Map(); // userId -> socketId
const typingUsers = new Map(); // conversationId -> Set(userId)

io.on('connection', (socket) => {
    console.log('A user connected:', socket.id);

    // Join room event
    socket.on('join_room', (room) => {
        socket.join(room);
        console.log(`User ${socket.id} joined room: ${room}`);
    });

    // Send message event
    socket.on('send_message', (data) => {
        // Broadcast to the specific room (conversation)
        io.to(data.room).emit('receive_message', data);
        console.log(`Message sent to room ${data.room}:`, data);
    });

    // Typing event
    socket.on('typing', (data) => {
        const { room, userId, userName } = data;
        if (!typingUsers.has(room)) {
            typingUsers.set(room, new Set());
        }
        typingUsers.get(room).add(userId);

        socket.to(room).emit('user_typing', { userId, userName });
    });

    // Stop typing event
    socket.on('stop_typing', (data) => {
        const { room, userId } = data;
        if (typingUsers.has(room)) {
            typingUsers.get(room).delete(userId);
            if (typingUsers.get(room).size === 0) {
                typingUsers.delete(room);
            }
        }
        socket.to(room).emit('user_stop_typing', { userId });
    });

    // User connected with ID
    socket.on('user_connected', (userId) => {
        onlineUsers.set(userId, socket.id);
        io.emit('online_users', Array.from(onlineUsers.keys()));
        console.log(`User ${userId} is online`);
    });

    socket.on('disconnect', () => {
        console.log('User disconnected:', socket.id);
        // Remove from online users
        for (const [userId, socketId] of onlineUsers.entries()) {
            if (socketId === socket.id) {
                onlineUsers.delete(userId);
                io.emit('online_users', Array.from(onlineUsers.keys()));
                console.log(`User ${userId} is offline`);
                break;
            }
        }
    });
});

const PORT = 3000;
server.listen(PORT, () => {
    console.log(`Socket.IO server running on port ${PORT}`);
});
