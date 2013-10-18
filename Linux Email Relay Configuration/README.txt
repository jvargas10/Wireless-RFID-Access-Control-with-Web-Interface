Email sent from server was configured as a GMAIL Relay.
The project assumes working in a Linux Environment (CentOS 6).


To accomplish email relayer, sendmail.mc file was changed. The change specifies gmail SMTP server as SMART_HOST, port 587 as RELAY_MAILER_ARGS and ESMTP_MAILER_ARGS, and finally the feature 'authinfo' defines the authentication for the gmail account.

FEATURE(`authinfo',`hash /etc/mail/auth/client-info.db')dnl
define(`SMART_HOST', `[smtp.gmail.com]')dnl
define(`RELAY_MAILER_ARGS', `TCP $h 587')dnl
define(`ESMTP_MAILER_ARGS', `TCP $h 587')dnl


The sendmail.mc if is found in /etc/mail directory.

Credentials for gmail account used are placed in a file named "client-info". This file is located in /etc/mail/auth. Once this file is created with your credentials, the following step is to create a hash map for the authentication file. This is done with the following command:

makemap hash client-info < client-info

This command will generate a file client-info.db in the same directory /(etc/mail/auth).
