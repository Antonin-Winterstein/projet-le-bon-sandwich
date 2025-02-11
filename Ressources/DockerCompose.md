# Mise en place du Docker

NB: Si vous avez déjà fait l'installation, placez-vous dans votre projet sandwich depuis votre terminal, puis faites un <b>docker-composer rm</b> puis, <b>y</b>.<br/>
Ensuite reprennez l'installation depuis le début <span style="font-size:0.6em;">(déso pas déso ^^')</span>

1. Pullez le projet sur votre machine locale.
2. Déplacez votre projet hors de votre répertoire WAMP/XAMP, de préférence dans les sous dossiers de User. (nous n'aurons plus besoin de machine locale, Docker remplacera cette machine).

3. Démarrez votre Docker.

4. Placez-vous, grâce à votre terminal, à la racine du projet, dans le dossier "<i>projet-le-bon-sandwich</i>".

5. Excécutez la commande <b>docker-compose up --no-start</b>.Patientez le temps que Docker télécharge les images nécéssaires et installe les containers.

#

    Creating network "projet-le-bon-sandwich_lbs.net" with driver "bridge"
    Creating projet-le-bon-sandwich_mongo.cat_1 ... done
    Creating projet-le-bon-sandwich_api.catalogue_1 ... done
    Creating projet-le-bon-sandwich_mongo-express_1 ... done
    Creating projet-le-bon-sandwich_api.commande_1  ... done

6. Executez la commande <b>docker-compose ps</b> afin de vous assurer que les 6 containers soient bien installés.

#

    projet-le-bon-sandwich_api.catalogue_1   docker-php-entrypoint start      Exit 0
    projet-le-bon-sandwich_api.commande_1    docker-php-entrypoint start      Exit 0
    projet-le-bon-sandwich_mongo-express_1   tini -- /docker-entrypoint ...   Exit 0
    projet-le-bon-sandwich_mongo.cat_1       docker-entrypoint.sh mongod      Exit 0

7. Démarrez vos containers avec la commande <b>docker-compose start</b>. Cette dernière permet de lancer tous les containers précédemment créé d'un seul coup de baguette magique ;-).

#

    Starting mongo.cat     ... done
    Starting api.catalogue ... done
    Starting api.commande  ... done
    Starting mongo-express ... done

8. Avant de vérifier si les containers sont fonctionnels n'oubliez pas de modifer votre fichier <b>hosts</b> (avec <b>sudo</b>) en rajoutant ces lignes :<br/>

#

    #Projet sandwich
    127.0.0.1 api.commande.local
    127.0.0.1 api.catalogue.local
    127.0.0.1 mongo.cat.local

9. Ceci étant fait, vous pouvez désormais consulter ces urls afin de vérifier une bonne fois pour toute le bon focntionnement de votre installation.<br/>

- <a href="https://api.commande.local:19043">API de prise de commandes</a></br>
  Acces possible pour la prise de commande :</br>

  - http://api.commande.local:19080/ </br>
  - https://api.commande.local:19043/ </br>

- <a href="https://api.catalogue.local:19143">API de navigation dans le catalogue</a></br>
  Acces possible pour la navigation dans le catalogue :</br>
  - http://api.catalogue.local:19180/ </br>
  - https://api.catalogue.local:19143/ </br>
- <a href="https://api.fidelisation.local:19243">API de navigation dans les fidelisations</a></br>
  Acces possible pour la navigation dans les fidelisations :</br>
  - http://api.fidelisation.local:19280/ </br>
  - https://api.fidelisation.local:19243/ </br>
- <a href="http://localhost:8081/">Acces Mongo Express</a></br>
- <a href="http://localhost:8080/">Acces Adminer</a></br>

10. Sur <a href="http://localhost:8080/">Acces Adminer</a> connectez-vous avec les identifiants suivants :

#

    Serveur : command
    MYSQL_USER=command_lbs
    MYSQL_PASSWORD=command_lbs
    MYSQL_DATABASE=command_lbs

11. Cliquez sur "Importer" dans le menu sur votre gauche puis séléctionnnez le fichier "<b>command_lbs.schema.sql</b>" dans votre repertoire "<b>lbs_commande_service/sql/</b>". Puis cliquez sur "Exécuter".

12. Repetez cette action (<b>⚠️ Il faut recliquer sur "Importer" dans le menu à gauche avant de séléctionner votre fichier</b>) en selectionnant maintennant le fichier "<b>command_lbs_data_1.sql</b>".

13. Pour MongoDB, connectez-vous sur http://localhost:8081/ et créez la base "<b>Catalogue</b>". Ensuite, connectez-vous dans le container Mongo avec la commande <b>docker exec -it projet-le-bon-sandwich_mongo.cat_1 /bin/bash</b>. </br> Vérifiez que vous êtes bien dans "<b>/var/data</b>" et faites un <b>ls -l</b>

#

    root@XXXXXXXXX:/var/data# ls
    categories.json  mongoimport  sandwichs.json

14. Ici exécutez les commandes

    - <b>mongoimport -d catalogue --collection categories --jsonArray < categories.json</b>
    - <b>mongoimport -d catalogue --collection sandwiches --jsonArray < sandwichs.2.json</b>

15. Maintenant nous allons installer notre "composer.json". Juste avant, aller dans votre fichier "<b>lbs_commande_service/src/</b>" et supprimer le fichier "<b>composer.lock</b>".<br/> Ceci étant fais, connectez-vous au container de l'API Commande avec la commande <b>docker exec -it projet-le-bon-sandwich_api.commande_1 /bin/bash</b>.
    Vous devriez vous retrouver dans le dossier "/var/www/src". Vérifiez le avec un petit <b>ls</b>

#

    root@XXXXXXXXXX:/var/www/src# ls
    api  composer.json

16. Votre "composer.json" est bien là, intaller le !</br>
    <b>composer install</b></br>
    Patientez lors du téléchargement, un petit café ?!

17. <span style="color:red;">⚠️ Vérifiez bien que votre vendor n'est pas push sur git (fichier "<b>.gitignore"</b>).</span> Normalement je l'ai ajouté mais vérifiez quand même ;).

18. Enfin, rendez-vous dans le "<b>lbs_commande_service/api/index.php</b>" et décommenter les lignes

#

    #require_once  __DIR__ . '/../src/vendor/autoload.php';

    #use \Psr\Http\Message\ServerRequestInterface as Request ;
    #use \Psr\Http\Message\ResponseInterface as Response ;

19. Faire la même chose pour catalogue (depuis l'étape 15)

© Antonin LIEHN

<!--

Toutes les commandes :
https://api.commande.local:19043/commandes

Tous les sandwiches :
https://api.catalogue.local:19143/sandwichs
Un sandwiche :
https://api.catalogue.local:19143/sandwichs/(ref)

 -->
