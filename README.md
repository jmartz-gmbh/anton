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

##### Setup
```
composer install
```

Now your ready to go.
```
./anton.sh
```

Add workspace/projects.json
```
{
    "ms-tracking": {
        "name": "Ms Tracking",
        "repo": "git@github.com:jmartz-gmbh/ms-tracking.git"
    }
}
```

##### Todo
* log errors and result in a build history
* move stuff into phar file

##### Backend
* notify (email, slack, webhook)
* trigger build via github webhook on push
* no direct access

##### Frontend
* add frontend based on boilerplate