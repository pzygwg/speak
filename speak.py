import socket
import threading
import netifaces as ni
from Crypto.Cipher import AES
from Crypto import Random
from Crypto.Util.Padding import pad, unpad
import base64

import select


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


def get_non_loopback_ip():
    for interface in ni.interfaces():
        if_addresses = ni.ifaddresses(interface)
        if ni.AF_INET in if_addresses:
            for address in if_addresses[ni.AF_INET]:
                if address['addr'] != '127.0.0.1':
                    return address['addr']
    return None

HOST = get_non_loopback_ip()

parts = HOST.split('.')
parts[-1] = '255'
broadcast_addr = '.'.join(parts)

print(f"Adresse de broadcast: {broadcast_addr}")
print(f"Adresse locale: {HOST}")


PORT = 12345
sock = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
sock.setsockopt(socket.SOL_SOCKET, socket.SO_BROADCAST, 1)
#sock.bind((HOST, PORT))
sock.bind(('0.0.0.0', PORT))



"""def send_message():
    while True:
        message = input("")
        if message.lower() == 'exit':
            break
        message = encrypt_message(message, ss)
        sock.sendto(message.encode(), (broadcast_addr, PORT))"""

def send_message():
    message = input("Entrez votre message (ou 'exit' pour quitter) : ")
    if message.lower() == 'exit':
        return False
    message = encrypt_message(message, ss)
    sock.sendto(message.encode(), (broadcast_addr, PORT))
    return True

"""def receive_message():
    while True:
        data, addr = sock.recvfrom(1024)
        print(f"{addr}: {decrypt_message(data.decode(),ss)} ({data.decode()})")"""

def receive_message():
    ready = select.select([sock], [], [], 0)
    if ready[0]:
        data, addr = sock.recvfrom(1024)
        print(f"{addr}: {decrypt_message(data.decode(),ss)} ({data.decode()})")


"""thread_receive = threading.Thread(target=receive_message)
thread_receive.start()

send_message()
thread_receive.join()

sock.close()"""

running = True
while running:
    running = send_message()
    receive_message()

sock.close()