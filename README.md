# Application de Chat UDP

Ceci est une application de chat UDP simple implémentée en Python. Elle permet aux utilisateurs sur le même réseau de communiquer entre eux en utilisant des messages de diffusion UDP.

## Fonctionnalités

- **Chiffrement :** Les messages sont chiffrés à l'aide du chiffrement AES pour une communication sécurisée.
- **Diffusion :** Les messages sont diffusés à tous les appareils sur le réseau local.
- **Interface Simple :** Les utilisateurs peuvent saisir des messages via l'interface de ligne de commande.

## Prérequis

- Python 3.x
- La bibliothèque `netifaces` (`pip install netifaces`)
- La bibliothèque `pycrypto` (`pip install pycryptodome`)

## Utilisation

1. Assurez-vous d'avoir Python installé sur votre système.
2. Installez les bibliothèques requises à l'aide de pip.
3. Exécutez le script `udp_chat.py`.
4. Saisissez vos messages lorsqu'on vous le demande.
5. Tapez 'exit' pour quitter l'application.

## Configuration

- Par défaut, l'application utilise le chiffrement AES avec une clé secrète prédéfinie. Vous pouvez modifier la variable `ss` dans le script pour une clé différente.
- Le port UDP utilisé par l'application est 12345. Assurez-vous que ce port n'est pas bloqué par votre pare-feu.

## Avertissement

- Cette application est destinée à des fins éducatives uniquement. Utilisez-la de manière responsable et respectez la vie privée des autres sur votre réseau.

## Contribution

Les contributions sont les bienvenues ! Si vous trouvez des problèmes ou avez des suggestions d'amélioration, veuillez ouvrir un problème ou créer une demande de tirage sur GitHub.

## Licence

Ce projet est sous licence MIT - voir le fichier [LICENSE](LICENSE) pour plus de détails.
