#!/bin/bash

database_name='postactiv'

db_password="$1"

# create extra user fields
db_query="ALTER TABLE profile ADD toxid text;
ALTER TABLE profile ADD matrix text;
ALTER TABLE profile ADD gpgpubkey text;
ALTER TABLE profile ADD xmpp text;"

mysql -u root --password="$db_password" -e "$db_query" $database_name
