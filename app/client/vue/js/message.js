var currentMessages = [];
    var lastMessageId = 0;

    document.getElementById('aes-key').addEventListener('input', function() {
        decryptMessages();
    });

    function sendMessage() {
        var messageInput = document.getElementById('compose-chat-box');
        var message = messageInput.value;
        var aesKey = document.getElementById('aes-key').value;
        if (!message || !aesKey) {
            console.error("Message or AES key is missing.");
            return;
        }

        var encrypted = CryptoJS.AES.encrypt(message, aesKey).toString();

        var xhr = new XMLHttpRequest();
        xhr.open("POST", "index.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onload = function () {
            if (xhr.status === 200) {
                console.log("Message sent successfully.");
                messageInput.value = ''; 
                fetchMessages();
            } else {
                console.error("Error sending message.");
            }
        };
        xhr.send("content=" + encodeURIComponent(encrypted));
    }

    function decryptMessages() {
        var aesKey = document.getElementById('aes-key').value;
        var messageElements = document.querySelectorAll('.content');

        messageElements.forEach(function(messageElement) {
            var encryptedContent = messageElement.getAttribute('data-encrypted-content');
            try {
                if (encryptedContent) {
                    var decryptedBytes = CryptoJS.AES.decrypt(encryptedContent, aesKey);
                    var decryptedMessage = decryptedBytes.toString(CryptoJS.enc.Utf8);
                    if (decryptedMessage) {
                        messageElement.textContent = decryptedMessage;
                    } else {
                        console.error('Decryption failed for a message, possibly due to an incorrect key.');
                    }
                }
            } catch (e) {
                console.error('An error occurred during decryption:', e);
            }
        });
    }

    function fetchMessages() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', `index.php?action=fetch_messages&lastId=${lastMessageId}`, true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                var newMessages = JSON.parse(xhr.responseText);
                if (newMessages.length > 0) {
                    lastMessageId = newMessages[0].idmsg; 
                    updateMessagesDisplay(newMessages);
                }
            }
        };
        xhr.send();
    }

    function updateMessagesDisplay(messages) {
        var messagesDisplay = document.getElementById('messages-display');
        messages.forEach(function(message) {
            if (document.querySelector(`[data-message-id="${message.idmsg}"]`)) return; 
            var messageElement = document.createElement('div');
            messageElement.className = 'message';
            messageElement.setAttribute('data-message-id', message.idmsg); 
            messageElement.innerHTML = `
                <img src="" alt="" class="chat-message-avatar">
                <div class="chat-messages">
                    <div class="chat-message-container">
                        <div class="chat-message">
                            <div class="chat-message-sender">${htmlspecialchars(message.auteur)}</div>
                            data-encrypted-content="${message.content}">
                            <span class="chat-message-time">${htmlspecialchars(message.date)}</span>
                        </div>
                    </div>`;
            messagesDisplay.appendChild(messageElement);
        });

        decryptMessages();
    }

    setInterval(fetchMessages, 5000);

    function htmlspecialchars(str) {
        if (typeof str === 'string') {
            str = str.replace(/&/g, '&amp;');
            str = str.replace(/</g, '&lt;');
            str = str.replace(/>/g, '&gt;');
            str = str.replace(/"/g, '&quot;');
            str = str.replace(/'/g, '&#039;');
        }
        return str;
    }