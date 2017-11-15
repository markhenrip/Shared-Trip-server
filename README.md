# Shared-Trip-server
the PHP back-end of our Shared-Trip project https://github.com/PpesR/Shared-Trip

## Basic outline  

This repo currently contains all the versions of PHP code that have ever been behind our app. 
This includes sloppy non-class files we wrote to test out a new functionality like database querys, image upload logic etc. 
At the moment of writing this there's an almost empty directory v1 where we'd like to put an actual API someday.

### First steps (End of 2nd and early 3rd iteration)

sharedtrip.php - our first attempt to communicate with database via PHP. 
Uses a simle SELECT * FROM query and only served as initial means of retrieving the trips/events for the main view. 
Currently discontinued and replaced by requestrouter.php and its helper files.   

### The "Parameter handler" architecture (3rd iteration)

requestrouter.php - a simple handler file that routes the request based on URI and/or request body parameters. 
Only works with GET and POST requests and has three helper files (St- stands for Shared-Trip): 
* StUser - handles basic user account related requests such as registration
* StEvent - adds, retrieves and searches events based on different criteria
* StAdmin - Approving and rejecting users and related informative queries

This is overall not a good architecture. 
The basic idea is that you always need to specify the handler name (hdl) and sometimes action name (act) which are then interpreted as in where to go for your desired query.
For example, requestrouter.php?hdl=event would give you all the events without specifyin any parameters, 
whereas hdl=admin and act=apr (for approval) also requires additional parameters event=<event id> and user=<user id>. 

### Plans for 4th iteration: REST(ish) API

We'll try our best to convert our existing solution into a "normal" API. 
We won't be using any framework and instead build it from scratch. 
This site http://coreymaynard.com/blog/creating-a-restful-api-with-php/ served as an 
inspiration for parts of current parameter-handler logic and will very likely be the base for the new architecure. 


