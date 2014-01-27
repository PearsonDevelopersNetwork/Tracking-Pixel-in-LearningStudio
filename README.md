Tracking Pixel in LearningStudio
================================

> #### Documentation Guide
> documentation/LICENSE.md  
> documentation/INSTALL.md  
> documentation/load-pixel-full-detail.js  


Overview
--------

While LearningStudio offers powerful analytics and enterprise reporting for online education programs, there is not currently a convenient way to track student progress through a course in terms of how far through the course they are. For example, there isn't a method for saying which students are 30% through the course, nor is there a way to tell the student which content item they should view next.

This example implements a tracking pixel in LearningStudio content items that allows you to see which pages the student(s) have visited. Leveraging this information can allow you greater insight into student pathways and usefulness of content. 

The student experience is not impacted because the tracking pixel is invisible. 


Requirements
------------

> Your server must run PHP 5.3.2 or greater.
> You should know how to use Git. 


Implementation & Getting Started
--------------------------------

> See documentation/INSTALL.md Guide for more details.



Compatibility 
-------------

 * This workflow should function in any browser supported by LearningStudio. 
 * The Like buttons can be implemented in Master courses and copied to course sections. 
 * The Like button can probably be implemented in Content Managed course content (content must be published for this to work). 
 * The Like button has not been tested with Equella-powered content. 


How It Works (Technology Used) 
------------------------------

 1. A JavaScript widget (load-pixel.js) parses the content item ID from the LearningStudio content frame. 
 
 2. Using the LearningStudio content extension JavaScript tools, available to the LearningStudio Visual Editor, retrieves the course ID and user ID. 
 
 3. When all three pieces of data are available, the JavaScript widget writes an <img> to the content item's HTML; the src attribute for the <img> points to a PHP file you host on your server.  
 
 4. The PHP-based tracker page uses the course and content IDs with LearningStudio's APIs to look up the course information, including the titles of the content and the course. It also calculates contextual information like next page, percentage complete, etc. You can enhance this page with the specific meaning and metrics you desire, or recording the information to a database. 

 5. The PHP tracker page renders an invisible 1x1 image to complete the cycle. 

Security & Privacy
------------------

This solution will create an encrypted hash of the user ID, course ID, and content item ID that is sent to the landing page. The landing page can decrypt this hash to get the information. This isn't inherently secure, because JavaScript-based encryption never truly can be; therefore this encryption is technically hackable. But it does offer security/privacy by obfuscation, preventing raw IDs from being in the wild on Facebook. Feel free to discuss this issue with the API Support team, if needed, but use of this tool is at your own risk. 


License
-------

> See LICENSE.md for full details.   

(c) 2014 Pearson.  MIT License  
Developed by the Pearson Developer Network along with ASU Online.   