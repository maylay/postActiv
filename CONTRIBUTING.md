Contributing to postActiv
================================================================================
First of all, if you're reading this intending to contribute to postActiv,
thanks!  Free software development only happens when people like you take an
interest in giving back to the software they themselves use, and their 
community.

There's a few files you should read before going forward with a merge request
or a patch submission.  They detail what this file touches on in brief.  They
are:

* CodingStandards.md: How your code should be structured and formatted to be 
                      accepted into the postActiv codebase.
* CodeOfConflict.md: Information about how conflict is handled in the review
                     process.
* SubmissionChecklist.md: A quick checklist to review before submission.


The Code of Conflict
--------------------------------------------------------------------------------
postActiv has a high submission standard and we want to keep quality code in the
codebase and bad code out of it.  As such your code will be closely scrutinized,
and you might take this criticism personally.  Please understand that this is
meant to keep the standards of the codebase up, and isn't meant personally.  All
the same, this isn't an excuse for poor behaviour, and a reviewer shouldn't be
misbehaving towards submitters.  The Code of Conflict outlines the dispute 
resolution mechanism if something does come up, so give it a read.


Coding Standards
--------------------------------------------------------------------------------
Since we will be expected to maintain your code once it's submitted, we ask you
to adhere to certain coding standards that make it easier for us to do so.  If
code doesn't follow them, it will be rejected, so please read up on these.

Bug Reports
--------------------------------------------------------------------------------
Please report bugs to the issue tracker at 
<https://git.gnu.io/maiyannah/postActiv/issues>  Avoid assigning the labels 
yourself, as these are for the development team to assign priority and area of 
coverage to a subject.  Please only submit something here if you are certain it
is a bug or represents a feature enhancement that we do not presently have.  If
you are uncertain whether it's a bug, please feel free to ask the users mailing
list at <users@postactiv.com>.

When reporting a bug, please try to include as much information as possible, 
including the environment being run on (if it's a common LAMP stack just give
us version numbers of the main stack components, that's fine), and the specific
error you get.  If you do not get a client-facing error, please check the PHP 
error_log and ensure there isn't something silently reported there, as well as
the postActiv log.  Try to include steps to reproduce the error as well, as if
we cannot reproduce the error, we can't fix it!

Branch of Code Submissions
--------------------------------------------------------------------------------
Unless you've been specifically directed otherwise, all submissions of code 
should be against the Nightly branch, so make sure any modifications are based 
on Nightly.


Copyright / Licensing
--------------------------------------------------------------------------------
You acknowledge that by submitting code to postActiv, you are licensing it under
the GNU AGPLv3 unless there is an extenuating circumstance where it would be
licensed differently (such as modifications to an external library we include
such as Stomp)

You also acknowledge that unless you assign a copyright explicitly, it will be
assumed to be assigned to postActiv.


Thanks for considering submission, and happy coding!