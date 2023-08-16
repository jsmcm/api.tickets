<?php

return [

    "name"          => "FluffyKids",
    "searchMailbox" => env("SUPPORT_SEARCH_MAILBOX", "UNSEEN"), // ALL, UNSEEN, SEEN
    "deleteMail"    => env("SUPPORT_DELETE_MAIL", false),
    "host"          => env("SUPPORT_HOST"),
    "port"          => env("SUPPORT_PORT"),
    "protocol"      => env("SUPPORT_PROTOCOL", "imap"),
    "userName"      => env("SUPPORT_USER_NAME"),
    "password"      => env("SUPPORT_PASSWORD"),
];
