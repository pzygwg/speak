<?php
$servername = "";
$dbname = ""; 
$username = "";
$password = "";

$messages = [];

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['content'])) {
        $auteur = 'NoBody';
        $content = isset($_POST['content']) ? $_POST['content'] : '';

        $content = strip_tags($content);

        $stmt = $conn->prepare("INSERT INTO msg (auteur, content, date) VALUES (:auteur, :content, CURDATE())");

        $stmt->bindParam(':auteur', $auteur);
        $stmt->bindParam(':content', $content);
        $stmt->execute();

        echo json_encode(["status" => "success", "message" => "Message sent successfully"]);
        exit;
    }
} catch(PDOException $e) {
    echo json_encode(["status" => "error", "message" => "Connection failed: " . $e->getMessage()]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['action']) && $_GET['action'] == 'fetch_messages') {
    $lastId = isset($_GET['lastId']) ? intval($_GET['lastId']) : 0;
    $stmt = $conn->prepare("SELECT * FROM msg WHERE idmsg > :lastId ORDER BY date DESC");
    $stmt->bindParam(':lastId', $lastId, PDO::PARAM_INT);
    $stmt->execute();
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($messages);
    exit;
}

require_once "../model/function.php";
verifierConnexion();

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>&copy; Speak </title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <main>
        <aside id="sidebar">
            <header id="sidebar-header">
                <img src="https://www.oddgifts.com/cdn/shop/products/article-2618985-1D7F051E00000578-78_634x390.jpg?v=1554590601" alt="" class="avatar" id="profile-image">
                <h3 class="titre">Compte Anonyme</h3>
                <div class="toolbar">
                    <div class="dropdown">
                        <img src="icons/menu.svg" alt="" class="icon dropdown-button">
                        <div class="dropdown-content">
                            <a href="deconnexion.php">Se déconnecter</a>
                        </div>
                    </div>
                </div>
            </header>
            <div class="connectivity-notification">
                <img src="icons/warning.svg" alt="Offline warning">
                <div>
                    <div class="connectivity-notification-title">
                        Votre session n'est pas connectée
                    </div>
                    <span>
                        Veuillez assurer une connexion stable pour continuer à parler
                    </span>
                </div>
            </div>
            <section id="sidebar-contents">
                <div id="chats-list">
                    <div class="chat-tile">
                        <img src="https://www.oddgifts.com/cdn/shop/products/article-2618985-1D7F051E00000578-78_634x390.jpg?v=1554590601" alt="" class="chat-tile-avatar">
                        <div class="chat-tile-details">
                            <div class="chat-tile-title">
                                <span>détenues de MVC</span>
                                <span>Aujourd'hui</span>
                            </div>
                            <div class="chat-tile-subtitle">
                                <span>Groupe Privé</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </aside>
        <section id="chat-window">
            <header id="chat-window-header">
                <img src="https://www.oddgifts.com/cdn/shop/products/article-2618985-1D7F051E00000578-78_634x390.jpg?v=1554590601" alt="" class="avatar" id="profile-image">
                <div id="active-chat-details">
                    <h3>détenues de MVC</h3>
                    <div class="info">Nombre de personnes actives : 0</div>
                </div>
                <div class="dropdown">
                    <img src="icons/communities.svg" alt="" class="icon dropdown">
                    <div class="dropdown-content contact-menu">
                        <a>Akira Santhakumaran</a>
                        <a>Logan Laporte</a>
                        <a>Loris Pensa</a>
                        <a>Jim Lainel</a>
                        <a>Luca Panossian</a>
                    </div>
                </div>
                <div class="dropdown">
                    <img src="icons/trace.svg" alt="" class="icon dropdown-button oeil">
                    <div class="dropdown-content contact-menu">
                        <a href="main.php">Masquer les messages avec AES</a>
                    </div>
                </div>
                <div id="chat-container">
                    <div id="key-input">
                        <input type="search" id="aes-key" placeholder="  Saisir la clé AES " class="compose-chat-box">
                    </div>
                </div>
            </header>
            <div id="chat-window-contents">
                <div class="datestamp-container">
                    <span class="datestamp">
                        9/02/2022
                    </span>
                </div>
                <div id="messages-display">
                <?php foreach ($messages as $message): ?>
                    <div class="chat-message-group">
                        <img src="https://www.oddgifts.com/cdn/shop/products/article-2618985-1D7F051E00000578-78_634x390.jpg?v=1554590601" alt="" class="chat-message-avatar">
                        <div class="chat-messages">
                            <div class="chat-message-container">
                                <div class="chat-message chat-message-first">
                                    <div class="chat-message-sender"><?php echo htmlspecialchars($message['auteur']); ?></div>
                                    <div class="content" data-encrypted-content="<?php echo htmlspecialchars($message['content']); ?>"></div>
                                    <span class="chat-message-time">00:00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
            </div>
            <footer id="chat-window-footer">
                <input type="search" name="message" id="compose-chat-box" placeholder="Écrire un message"
                        autocomplete="off">
                <button onclick="sendMessage()"><img src="icons/trace2.svg" alt="" class="icon oeil"></button>
            </footer>
        </section>
    </main>
    <script src="js/scroll.js"></script>
    <script src="js/connexion.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js"></script>
<script>
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
                    lastMessageId = newMessages[0].idmsg; // Mettre à jour lastMessageId
                    updateMessagesDisplay(newMessages);
                }
            }
        };
        xhr.send();
    }

    function updateMessagesDisplay(messages) {
        var messagesDisplay = document.getElementById('messages-display');
        messages.forEach(function(msg) {
            if (document.querySelector(`[data-message-id="${msg.idmsg}"]`)) return; 
            var messageGroup = document.createElement('div');
            messageGroup.className = 'chat-message-group';
            messageGroup.innerHTML = `
                <img src="https://www.oddgifts.com/cdn/shop/products/article-2618985-1D7F051E00000578-78_634x390.jpg?v=1554590601" alt="" class="chat-message-avatar">
                <div class="chat-messages">
                    <div class="chat-message-container">
                        <div class="chat-message chat-message-first">
                            <div class="chat-message-sender">${htmlspecialchars(msg.auteur)}</div>
                            <div class="content" data-encrypted-content="${htmlspecialchars(msg.content)}"></div>
                            <span class="chat-message-time">00:00</span>
                        </div>
                    </div>
                </div>`;
            messagesDisplay.appendChild(messageGroup);
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
</script>
</body>
</html>
