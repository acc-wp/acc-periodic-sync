=== ACC Periodic Sync ===
Contributors: Karine Frenette-G, Francois Bessette
Tags: 
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Periodic synchronization of ACC members list. An add-on to ACC User Importer plugin.

== Description ==
This plugin is an add-on to the Alpine Club of Canada (ACC) User Importer plugin.
It schedules two periodic tasks:

* Membership update
   This task pulls the membership list from the National website and adds new
   members to the local website. The role assigned to new members is configurable.

* Membership Expiry:
   This task goes though the local membership list and looks for entries with
   expiry date older than the current time. The role of such members is changed
   to be one of two configurable options. For example, the member role can be
   initially set to "expired", then after one month it can become "ex-member".

The plugin provides the following 3 web pages for configuration:
**ACC Admin**
    -how to access to the National website
    -What role to assign new members
    -What role to assign to members right after their membership is expired
    -What role to assign to members 1 month after their membership is expired
    -A button to maually trigger the Membership update
    -logs of the tasks
**ACC Email Templates**
    -templates for email to send to new users or expired users
**ACC Cron Jobs**
    -timers intervals for the two taks to periodically run.


== Installation ==
1. Make sure the ACC User Importer plugin is installed and activated.
1. install "acc-periodic-sync.zip".
1. Activate the plugin.


== Changelog ==
= 1.3.0 =
* changé le nom du plugin pour ACC Periodic Sync
* changé la version à 1.3.0
* liste des logs en ordre de date
* changé le nom du répertoire du plugin de "karinegaufre" à "acc-periodic-sync"
* Francisation
* Enlever le code qui ajoute un timer de 30s (non nécessaire)
* Changer la valeur par défaut des timers (2x par jour, 1 semaine)
* Renommer Cron Manager pour ACC Cron Jobs
* Renommer Email Templates pour ACC Email Templates
* Enlevé du code qui était commenté
* Logs: ajouté le nombre de membres dont le role a été rafraichi.

