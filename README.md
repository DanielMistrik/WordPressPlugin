# WordPressPlugin
## Rationale
This plugin is only one part of a three-part solution, called Alias, to address transparent data usage by eshops, the other two being a server and an app (neither have code on this repository). Seeing increasing demand for data users to divulge how and when they use user data this plugin aims to address increasing user demand to know what happens with their data while providing a largely autonomous solutions so eshops aren't burdened with extra responsibilities.
## How it Works
Each time an eshop accesses a piece of the user's data a data access request is created and sent to the user who can view this information through the alias app. The plugin is responsible for registering the data accesses made by the eshop and reporting them to the server. It therefore needs to run on a service or framework that eshops use frequently and where they manage most of their user data, this fits Wordpress perfectly. In addition to reporting data access requests to the server the plugin also displays them to the eshop so it can use the data access hash, a unique identifier for each data access request which the eshop can use as proof of transparently acquiring user data, in filling out order or sending emails.\
\
The plugin has a GUI which is where the data access requests will be presented, in a table format, and where the admins of the eshop can manually add a data access request. The plugin also automatically tracks data accesses and works with the woocommerce plugin, a very popular add-on for woocommerce, to increase the scope of tracking data accesses. \
\
The GUI part of the plugin can be accessed by clicking on the settings menu which opens a dropdown menu at the bottom of which there should be the alias menu, dont forget to activate the plugin first.
## Technical Details
### Scripts
* alias-plugin.php - The primary file of the plugin. It is the first file called and sets up the alias plagin page in the settings menu and calls templates/admin.php to populate the page. Is resposnible for all automatic data access tracking, so basically everything the user doesn't see.
* templates/admin.php - File responsible for the GUI part of the plugin. Everything the user sees in the alias GUI is done by this file, populating the table of past data access requests and the manual data access request add.
* ALS_db_file.php - The php file defining the ALS_Receipts database. Is called by alias-plugin.php .
* index.php - Standard wordpress plugin file.
* uninstall.php - Standard wordpress plugin file for uninstalling.
### Databases
* ALS_Receipts - Database that acts as a local storage for all data access requests. The table the admins see in the GUI part of the plugin is populated from this database.
## Additional Notes
While all API calls are prepared the plugin IS NOT connected to a server so running the plugin will not provide all the functionality described above. It will however register the data accesses locally and present them in a table in the GUI part of the plugin. 
## External Sources
*  Chosen by Patrick Filler (MIT License)
