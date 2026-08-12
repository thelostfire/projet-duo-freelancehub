# Freelance Hub
## Projet fait en collaboration avec [darkwuty](https://github.com/darkwuty)

Hey!
Pour tester la création de client dans la BDD il faut mettre en place le JWT car j'ai mit un champ NOT NULL pour 'owner'
Je pense que pour l'instant les relations dans la BDD c'est good, à voir par la suite si on ajoute d'autres entités?

<em>Hey, on va tester sans le jwt d'abord, je l'ajoute plus tard que prévu (je commence par une auth classique faite avec symfony)</em>
<em>J'ai créé une page de login (tu vas sur l'addresse du site en local quand tu le fais tourner avec 'symfony -serve', et ajoute /login à la fin), faudra un système d'inscription, en attendant voilà comment je vois les choses:
> En lançant le site on arrive sur une page qui demande de s'auth (ou s'inscrire)
> Une fois login ça nous met direct sur un tableau de bord avec les clients etc
> Je mettrai en place un ssytème de voters pour qu'un USER ne voit que ses clients, factures, etc
>> Cela allègera le nombre de rôles, d'ailleurs je me demande si on a besoin de plusieurs rôles ? Un admin pour tout gérer au cas où en plus des USERs classiques, mais je vois mal un intéret d'aller plus loin..

J'ai aussi rajouté un champ isVerified à user, c'est pour plus tard atm c'est tjrs true</em>
