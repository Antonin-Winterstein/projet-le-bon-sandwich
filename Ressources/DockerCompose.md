# Mise en place du Docker
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
<a href="https://api.commande.local:19043">API de prise de commandes</a></br>
<a href="https://api.catalogue.local:19143">API de navigation dans le catalogue</a></br>
<a href="http://localhost:27018/">Acces Mongo Express</a></br>