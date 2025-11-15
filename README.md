===========================================
DC Download Counter – Joomla 5/6 Plugin
===========================================

A lightweight and free Joomla plugin that tracks unique clicks and file downloads using a simple shortcode. 
The plugin adds an automatic counter badge next to each tracked link and works with both local and external URLs.

------------------------------------------------
Features
------------------------------------------------
• Tracks unique clicks based on IP address  
• Displays a badge with the current download count  
• Supports local files and external links (PDF, ZIP, GitHub, cloud storage, etc.)  
• Easy-to-use shortcode: {download href="URL"}Text{/download}  
• Works with TinyMCE and other editors that strip CSS classes  
• Zero dependencies – no jQuery, no extra libraries  
• Logs stored in lightweight text files (no database usage)  
• Fully compatible with Joomla 5 and Joomla 6  

------------------------------------------------
Installation
------------------------------------------------
1. Download the ZIP package.  
2. Go to Joomla Administrator → Extensions → Install.  
3. Upload the ZIP file.  
4. Enable the plugin: Extensions → Plugins → System – DC Download Counter.  
5. (Optional) Configure badge colors and size in plugin settings.  

------------------------------------------------
Usage
------------------------------------------------
Wrap any link with the shortcode:

{download href="https://example.com/file.pdf"}Download PDF{/download}

Examples:
• PDF file:  
  {download href="/docs/catalog.pdf"}Download catalog{/download}

• ZIP archive:  
  {download href="/downloads/archive.zip"}Download ZIP{/download}

• GitHub link:  
  {download href="https://github.com/user/repo"}GitHub repository{/download}

• Image as a link:  
  {download href="/docs/file.pdf"}<img src="/images/button.png" alt="Download">{/download}

------------------------------------------------
How the counter works
------------------------------------------------
• Each tracked link gets its own log file located in:  
  /media/plg_system_dcdownloadcounter/logs/

• Each unique IP is counted once.

• The number of lines in the log file = total downloads.

• To reset a counter, simply delete the corresponding .log file.

------------------------------------------------
Compatibility
------------------------------------------------
• Joomla 5.x  
• Joomla 6.x  
• PHP 8.1 – 8.3  

------------------------------------------------
License
------------------------------------------------
GNU General Public License v3.0

------------------------------------------------
Developed by
------------------------------------------------
Design Cart – https://www.designcart.pl
Free extensions, tools & LAB experiments for Joomla and e-commerce.
