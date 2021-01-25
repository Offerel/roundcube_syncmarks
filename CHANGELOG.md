### v2.2.5
- Fix to open ambiguous folders
- Fix for empty push response
- Removed date header
  
### v2.2.4
- Change to http POST
  
### v2.2.3
- Added preloading animation
  
### v2.2.2
- Fix for notifications
- Fix for unvisible settings
  
### v2.2.0
- Added notifications for pushed links

### v2.1.2
- Moved the get_bookmarks function from init to click to speed up inital loading

### v2.1.1
- Moved the get_bookmarks function from init to click to speed up inital loading
  
### v2.1.0
- Added support for SyncMarks
- Renamed to Roundcube Sycmarks

### v2.0.3
- Added support for Rouncube 1.4 elastic skin
  
### v2.0.2
- changed composer.json
  
### v2.0.1
- Added Chrome Extension

### v2.0.0
- The bookmarks saved with DAVMarks AddOn (https://addons.mozilla.org/en-US/firefox/addon/davmarks/) are now supported. Change $config['bookmarks_path'] to the WebDAV url (foldername) to your WebDAV share and $config['bookmarks_filename'] to "bookmarks.json". For the username and password, the Roundcube credentials are used.

### v1.2.0
- delete bookmarks with right-click
- retrieve bookmark now via curl instead file_get_contents
- bug fixes

### v1.1.0
- Add bookmarks via button
- automatically import bookmarks to Firefox file at Firefox start

### v1.0.3
 - fix JavaScript for initialization error
 - fix css for toolbar button

### v1.0.2
 - small fix for a error message

### v1.0.1
 - fixed open links in other tab

### v1.0.0
 - Initial version