class AntiSpam {
    constructor(limit, interval) {
        this.limit = limit;
        this.messages = 0;
        setInterval(() => {
            if (this.messages > 0) this.messages--;
        }, interval);
    }

    canSend() {
        return this.messages < this.limit;
    }

    increment() {
        this.messages++;
    }
}

class Chat {
    constructor(socketUrl) {
        this.socket = new WebSocket(socketUrl);
        this.username = null;
        this.chatBox = document.getElementById('chatBox');
        this.messageInput = document.getElementById('messageInput');
        this.antiSpam = new AntiSpam(10, 2000);

        this.setupSocketEvents();
        this.setupDOMEvents();
        this.setupPanic();
    }

    setupPanic(){
        document.addEventListener('keydown', (e) => {
            const key = e.key.toLowerCase();
            if (e.ctrlKey && key === 'q') {
                this.panic();
            }
        });
        document.addEventListener('click', (e) => {
            if(e.target == document.body){
                this.panic();
            }
        });
    }

    panic(){
        window.open('https://www.bing.com/search?q=outlook', '_blank')
        window.location.replace('about:blank');
    }

    setupAniContext(){
        document.documentElement.addEventListener('contextmenu', (e)=>{
            e.preventDefault();
        })
        document.addEventListener('keydown', (e) => {
            const key = e.key.toLowerCase();
            if (e.ctrlKey && e.shiftKey && (key === 'i' || key === 'c')) {
                e.preventDefault();
            }
        });
    }

    setupSocketEvents() {
        this.socket.addEventListener('open', () => {
            console.log('Connected to the WebSocket server');
        });

        this.socket.addEventListener('message', (event) => this.handleMessage(event));

        this.socket.addEventListener('error', (error) => {
            console.error(`WebSocket error: ${error}`);
        });

        this.socket.addEventListener('close', () => {
            console.log('Disconnected from the WebSocket server');
        });
    }

    setupDOMEvents() {
        document.getElementById('sendButton').addEventListener('click', () => this.sendMessage());
        this.messageInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') this.sendMessage();
        });

        window.onload = () => this.loadMessages();
    }

    handleMessage(event) {
        const data = JSON.parse(event.data);
        const fromUser = this.htmlEncode(data.usr_from);
        let message = this.htmlEncode(data.message);

        if (fromUser === 'Auto Admin' && this.username) {
            const escapedUsername = this.username.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            const pattern = new RegExp(`\\b${escapedUsername}\\b`, 'g');
            message = message.replace(pattern, 'You');
        }

        if (fromUser === 'PANIC' && message == 'PANIC') {
            this.panic();
        }
        if (fromUser === 'CLEAR' && message == 'CLEAR') {
            Array.from(this.chatBox.children).forEach(child => {
                child.remove();
            });            
        } else{
            const displayUser = fromUser === this.username ? 'You' : fromUser;

            this.addMessageToChat(displayUser, message);
        }
    }

    sendMessage() {
        const messageText = this.messageInput.value.trim();
        if (!messageText || messageText.length > 600 || !this.antiSpam.canSend()) return;

        this.antiSpam.increment();
        this.socket.send(JSON.stringify({ 'text': messageText, 'session': this.getCookie('session') }));
        this.messageInput.value = '';
    }

    addMessageToChat(fromUser, message) {
        const messageElement = document.createElement('div');
        messageElement.classList.add('message');
        messageElement.innerHTML = `<span class="sender">${fromUser}:</span> ${message}`;
        this.chatBox.appendChild(messageElement);
        this.chatBox.scrollTop = this.chatBox.scrollHeight;
    }

    htmlEncode(str) {
        return str.replace(/&/g, "&amp;").replace(/>/g, "&gt;").replace(/</g, "&lt;").replace(/"/g, "&quot;");
    }

    loadMessages() {
        fetch('/api/messages')
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                if (data.status === 'error') {
                    alert(data.message);
                    return;
                }
                if(!data.admin){
                    this.setupAniContext();
                } else{
                    document.querySelector('h3').textContent += ' For global - (CTRL + S)';
                    document.addEventListener('keydown', (e) => {
                        const key = e.key.toLowerCase();
                        if (e.ctrlKey && key === 's') {
                            e.preventDefault();
                            this.messageInput.value = '/panic';
                            this.sendMessage();
                        }
                    });
                }
                this.username = data.username;
                data.messages.forEach(msg => {
                    this.addMessageToChat(this.htmlEncode(msg.usr_from), this.htmlEncode(msg.message));
                });
            })
            .catch(error => alert(error));
    }

    getCookie(name) {
        const value = `; ${document.cookie}`;
        const parts = value.split(`; ${name}=`);
        return parts.length === 2 ? parts.pop().split(';').shift() : '';
    }
}

// Instantiate the chat class
const chatApp = new Chat('wss://chat-v1-api.mjdawson.net:441');
