import express from 'express';
import { createServer as createHttpServer } from 'http';
import { createServer as createHttpsServer } from 'https';
import { Server } from 'socket.io';
import fs from 'fs';

const app = express();
let server;

// Replace these paths with the actual paths to your SSL certificates on your live server
// Example for Let's Encrypt: '/etc/letsencrypt/live/task.coxfuture.com/privkey.pem'
const privateKeyPath = '/path/to/your/private.key';
const certificatePath = '/path/to/your/certificate.crt';

if (fs.existsSync(privateKeyPath) && fs.existsSync(certificatePath)) {
    // Run in HTTPS mode if certificates exist
    const privateKey = fs.readFileSync(privateKeyPath, 'utf8');
    const certificate = fs.readFileSync(certificatePath, 'utf8');
    const credentials = { key: privateKey, cert: certificate };
    
    server = createHttpsServer(credentials, app);
    console.log('Running Socket.IO server in Secure HTTPS mode');
} else {
    // Fallback to HTTP mode (for local XAMPP development)
    server = createHttpServer(app);
    console.log('Running Socket.IO server in HTTP mode (No SSL certificates found)');
}

const io = new Server(server, {
    cors: {
        origin: '*', // Allow all origins for simplicity in dev
        methods: ['GET', 'POST']
    }
});

const onlineUsers = new Map(); // userId -> Set(socketId)
const typingUsers = new Map(); // conversationId -> Set(userId)

io.on('connection', (socket) => {
    console.log('A user connected:', socket.id);

    // Join room event
    socket.on('join_room', (room) => {
        socket.join(room);
    });

    // Send message event
    socket.on('send_message', (data) => {
        if (!data) return;
        // Broadcast to the specific room (conversation)
        io.to(data.room).emit('receive_message', data);

        // Immediately emit delivery confirmation back to sender
        // (shows double gray ticks as soon as any online user in the room gets it)
        if (data.message && data.message.id) {
            socket.to(data.room).emit('message_delivered', {
                messageId: data.message.id,
                room: data.room
            });
        }
    });

    // Read message event
    socket.on('messages_read', (data) => {
        if (!data) return;
        // Broadcast to the specific room that messages were read
        socket.to(data.room).emit('messages_read_by_user', data);
    });

    // Typing event
    socket.on('typing', (data) => {
        if (!data) return;
        const { room, userId, userName } = data;
        if (!typingUsers.has(room)) {
            typingUsers.set(room, new Set());
        }
        typingUsers.get(room).add(userId);

        socket.to(room).emit('user_typing', { userId, userName, room });
    });

    // Stop typing event
    socket.on('stop_typing', (data) => {
        if (!data) return;
        const { room, userId } = data;
        if (typingUsers.has(room)) {
            typingUsers.get(room).delete(userId);
            if (typingUsers.get(room).size === 0) {
                typingUsers.delete(room);
            }
        }
        socket.to(room).emit('user_stop_typing', { userId, room });
    });

    // User connected with ID
    socket.on('user_connected', (userId) => {
        if (!onlineUsers.has(userId)) {
            onlineUsers.set(userId, new Set());
        }
        onlineUsers.get(userId).add(socket.id);
        io.emit('online_users', Array.from(onlineUsers.keys()));
    });

    socket.on('disconnect', () => {
        console.log('User disconnected:', socket.id);
        // Remove from online users
        for (const [userId, socketIds] of onlineUsers.entries()) {
            if (socketIds.has(socket.id)) {
                socketIds.delete(socket.id);
                if (socketIds.size === 0) {
                    onlineUsers.delete(userId);
                    io.emit('online_users', Array.from(onlineUsers.keys()));
                }
                break;
            }
        }
    });
});

const PORT = 3000;
server.listen(PORT, () => {
    console.log(`Socket.IO server running on port ${PORT}`);
});
