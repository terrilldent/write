write
=====

PHP/MySQL Blogging engine with enough security that you can use it on a library computer without HTTPS and not be worried about someone compromising your account credentials or session.

The goal is to have strong authenticity, not privacy. Most of the articles I write end up being published, and I am not trying to keep drafts secret. Text of articles are sent in the clear if not used with HTTPS. 

Threat Model
------------

These are the primary threats I am trying to address: 

1) Proving credentials, without compromising the password. The password is always hashed and is never stored or saved in plaintext.

2) Prevent Replay Attacks. Prevent an evesdropper from using a hashed password or session token to formulate their own request.

3) Session hijacking. Prevent an evesdropper from compromising an active session to perform their own requests. 



This is a work in progress, but functional enough that I use it myself.




