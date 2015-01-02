write
=====

`work in progress` PHP/MySQL/JS Blogging engine with enough security that you can use it on an untrusted network without HTTPS and not be worried about someone compromising your account credentials or session.

The user interface is a single page web app. It includes a real-time render as you type, and auto save. 


Security Model
------------

The goal is to have strong authenticity and integrity, not confidentiality. It does not keep the content of blog posts secret. Text of articles are sent in the clear. It tries to prevent unintended operation or compromise control of your account. 

These are the primary threats I am trying to address: 

1) *Proving credentials, without compromising the password*. The password is always hashed and is never stored or saved in plaintext.

2) *Prevent Replay Attacks and Session Hijacking*. Prevent an evesdropper from using a hashed password or session token to compromising an active session and perform their own requests. 

Cookies are not used, instead all the JavaScript requests contain an auth token which is updated frequently.

3) *Message Integrity*. Check that outgoing and incoming requests were not modified in transit.


Status
------

This is a work in progress, but functional enough that I use it myself.

* Authentication currently relies on having roughly synchronized time on the client and server. I plan to change this in the future.

* I am not protecting against modification of the payload while in transit. 

