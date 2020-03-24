#### Anton Build Server

##### Description
This is a simple Build Server which is my replacement for Jenkins with Robo.
For me Jenkins with Robo works well, but i want a light weight solution for it.
So i will try to create a cli trigger build.
Additional i will add a pwa to handle this and a lumen webhook to trigger builds via github push.

##### Security
For Security reasons i would highly recommend you to run this server not public.
Only people in your network should have access to this server.
If you need to work from outside of your company buiding, use a vpn to access the network.

##### Todo
* validate config in Collector
* log errors and result in a build history
* create logic to trigger builds by queue.json
* move stuff into phar file
* use composer to load stuff
* move stuff into modules

##### Later
* add frontend with boilerplate
* lumen api to trigger build via webhook
* notify email
* notify slack
* notify nova ?
* lumen only read access

##### Classes
Trigger - Class to trigger a build with project name and pipeline name
Collector - Class to collect all projects and prepare workspace
Queue - Class to trigger builds by queue.json