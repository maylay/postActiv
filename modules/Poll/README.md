Unfinished basic stuff:
------------------------
* make pretty graphs for response counts
* ActivityStreams output of poll data is temporary; the interfaces need more flexibility
* ActivityStreams input not done yet
* need link -> show results in addition to showing results if you already voted
* way to change/cancel your vote

Known issues:
--------------
* HTTP caching needs fixing on show-poll; may show you old data if you voted after
* Breaks in MySQL 5.7+ default configuration due to `SQL_MODE "ONLY_FULL_GROUP_BY"`.
This can be resolved by setting `SQL_MODE` to `STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION`

Things todo:
---------------
* should we allow anonymous responses? or ways for remote profiles to respond locally?

Fancier things todo:
---------------------
* make sure backup/restore work
* make sure ostatus transfer works
* a way to do poll responses over ostatus directly?
* allow links, tags, @-references in poll question & answers? or not?

Storage todo:
----------------
* probably separate the options into a table instead of squishing them in a text blob
