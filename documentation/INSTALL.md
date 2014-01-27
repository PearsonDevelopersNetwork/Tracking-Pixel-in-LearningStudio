Implementing a Tracking Pixel in LearningStudio
=============================================

Cloning the Repository 
----------------------

When cloning the repository, use a recursive clone, i.e.: 

    git clone https://github.com/PearsonDevelopersNetwork/Tracking-Pixel-in-LearningStudio.git




Implement an Tracking Pixel PHP Page
------------------------------------

You need to provide a publicly accessible intermediary page that will serve as the location for the tracking pixel. Use the server/tracker.php as starting point and modify it to interact with your database or other systems. This file receives the information from LearningStudio, runs some API calls, and assembles some sample variables you use can use to track the student location in a course. You can also modify this with additional API calls or to find additional information. 

Once this file completes its processing, it outputs a blank pixel image that won't impact your content's look and feel. 


Host the JavaScript Code Somewhere
---------------------------------------

When you implement the tracking file, you should also host the `js/load-pixel.js` file provided here. This script handles all the work of preparing and rendering the tracing pixel in a LearningStudio content item. Make note of the publicly accessible URL where this file can be loaded. 


Implement the HTML in a LearningStudio Content Item
---------------------------------------------------

To implement the code in LearningStudio, you will need the URL for the tracker page you created above, and the URL pointing to `load-pixel.js`. 

In LearningStudio Author mode, choose a content item and start editing it. Use "HTML" mode in the Visual Editor (the button is at the bottom of the editor). Important: Do *not* use "Plain Text Editor" because this will sanitize your HTML tags. 

In HTML mode, copy and paste this code anywhere in the code (usually at the top of bottom), replacing the `{parameters}` with the appropriate values. You only need to do this part once for each content item, but these values will be the same for all content items and all courses.

    <script type="text/javascript">
        var TrackingFileLocation = '{URL_OF_TRACKING_PAGE}';
    </script>
    <script type="text/javascript" src="{URL_TO_LOAD-PIXEL.JS}"></script>

Save and publish the Content Item. The tracking pixel will load asynchronously once the document is ready. 

