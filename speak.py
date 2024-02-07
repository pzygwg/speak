import socket
import threading
from Crypto.Cipher import AES
from Crypto import Random
from Crypto.Util.Padding import pad, unpad
import base64
import struct  # Ajoutez cette ligne

ss = b'44778645bb4a3bd00aec273a8212fe4c'

def encrypt_message(message, key):
    iv = Random.new().read(AES.block_size)
    cipher = AES.new(key, AES.MODE_CBC, iv)
    padded_message = pad(message.encode(), AES.block_size)
    encrypted = cipher.encrypt(padded_message)
    return base64.b64encode(iv + encrypted).decode()

def decrypt_message(encrypted_message, key):
    encrypted_message_bytes = base64.b64decode(encrypted_message)
    iv = encrypted_message_bytes[:AES.block_size]
    cipher = AES.new(key, AES.MODE_CBC, iv)
    padded_message = cipher.decrypt(encrypted_message_bytes[AES.block_size:])
    return unpad(padded_message, AES.block_size).decode()

MULTICAST_GRP = '224.0.0.1'
MULTICAST_PORT = 12345

def get_non_loopback_ip():
    try:
        with socket.socket(socket.AF_INET, socket.SOCK_DGRAM) as s:
            s.connect(("8.8.8.8", 80))
            ip = s.getsockname()[0]
            return ip
    except Exception:
        return None

HOST = get_non_loopback_ip()
print(f"Adresse locale: {HOST}")

sock = socket.socket(socket.AF_INET, socket.SOCK_DGRAM, socket.IPPROTO_UDP)
sock.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)
sock.bind(('', MULTICAST_PORT))
mreq = struct.pack("4sl", socket.inet_aton(MULTICAST_GRP), socket.INADDR_ANY)
sock.setsockopt(socket.IPPROTO_IP, socket.IP_ADD_MEMBERSHIP, mreq)

def send_message():
    while True:
        message = input("")
        if message.lower() == 'exit':
            break
        message = encrypt_message(message, ss)
        sock.sendto(message.encode(), (MULTICAST_GRP, MULTICAST_PORT))

def receive_message():
    while True:
        data, addr = sock.recvfrom(1024)
        print(f"{addr}: {decrypt_message(data.decode(), ss)} ({data.decode()})")

thread_receive = threading.Thread(target=receive_message)
thread_receive.start()

send_message()
thread_receive.join()

sock.close()
