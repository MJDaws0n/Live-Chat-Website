Live Chat Application
=====================

This is an open-source live chat application that allows users to communicate in real-time. It features a responsive design, theme toggling, and WebSocket integration for seamless messaging.

An example is at [https://mjdawson.net/chat](https://mjdawson.net/chat)

SERVER APPLICAITON REQUIRED, GET FROM https://github.com/MJDaws0n/Chat-Website-Server

Features
--------

*   **Real-time Messaging:** Communicate instantly with other users.
*   **Theme Toggling:** Switch between light and dark themes.
*   **Responsive Design:** Works on various screen sizes.
*   **WebSocket Communication:** Ensures fast and reliable message transmission.
*   **Code based system:** Ensure users use a valid code to login.

Installation
------------

Rename .env_example to .env and set the values in there for a DB
Install DB from the database.sql file, i can't remeber if it sets it up automatically, so if you get an error just add it.
To create your first user you must do it manually in the database, do do that the password value doesnt matter you will delete this user afterwards, what matters is setting addition values to {"name":"whatever", "admin":"true"} , then set the sesison value to something, then create a cookie in your browser called 'session' and set it to that session you set in the database. Then in your browser go to https://{the domain}/api/user/create?name=Jeff and set the name to whatever you want and then take note of the code that comes from that, that is the logon code for that account, then go to the database and set that user to your infomation and from then on, you can create users from there and the reset of the stuff is handled from slash commands i forgot but check out the server github repos code and it's easy to find.
Need to make this, but im sure you can figure it out or just ask chatgpt
    

Usage
-----

1.  Open your browser and navigate to the website hosting the site (must be a PHP server)
2.  Use the input box to type and send messages.
3.  Toggle themes using the "Toggle Theme" button.
4.  Setup users in the datbase

TODO
-----
- Allows users to request a code.
- Make this readme more detailed.

License
-------

This project is licensed under the MIT License. See the LICENSE file for details.
