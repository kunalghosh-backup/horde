Þ                      x     y       ~  §  $   &  d   K  r   °  S   #  9   w  ±  ±     c  /     4   ±     æ  h     8   o    ¨  6   /	  1   f	  T  	  8   í
  u   &  f     Z     K   ^  å  ª  0     W   Á  9     ?   S       J                                            	                           
           %s is no local file! Action %s not supported! Activates the IMAP debug log. This will log the full IMAP communication - CAUTION: the "php" driver is the only driver variant that does not support this feature. For most drivers you should use "STDOUT" which will direct the debug log to your screen. For the horde, the horde-php, and the roundcube drivers you may also set this to a filename and the output will be directed there. Deactivate caching of the IMAP data. Path to the configuration file. Comman line parameters overwrite values from the configuration file. Produce time measurements to indicate how long the processing takes. You *must* activate logging for this as well. Report memory consumption statistics. You *must* activate logging for this as well. Sets the connection type. Use either "tls" or "ssl" here. The Kolab backend driver that should be used.
Choices are:

 - horde     [IMAP]: The Horde_Imap_Client driver as pure PHP implementation.
 - horde-php [IMAP]: The Horde_Imap_Client driver based on c-client in PHP
 - php       [IMAP]: The PHP imap_* functions which are based on c-client
 - pear      [IMAP]: The PEAR-Net_IMAP driver
 - roundcube [IMAP]: The roundcube IMAP driver
 - mock      [Mem.]: A dummy driver that uses memory. The host that holds the data. The password of the user accessing the backend. The port that should be used to connect to the host. The user accessing the backend. Write a log file in the provided LOG location. Use "STDOUT" here to direct the log output to the screen. [options] MODULE ACTION

Possible MODULEs and ACTIONs:

 Project-Id-Version: Horde_Kolab_Cli
Report-Msgid-Bugs-To: dev@lists.horde.org
POT-Creation-Date: 2011-11-21 18:18+0100
PO-Revision-Date: 2012-09-08 11:45+0900
Last-Translator: Hiromi Kimura <hiromi@tac.tsukuba.ac.jp>
Language-Team: i18n@lists.horde.org
Language: 
MIME-Version: 1.0
Content-Type: text/plain; charset=UTF-8
Content-Transfer-Encoding: 8bit
Plural-Forms: nplurals=1; plural=0;
 %s ã¯ã­ã¼ã«ã«ãã¡ã¤ã«ã§ã¯ããã¾ããï¼ åä½ %s ã¯ãµãã¼ãããã¦ãã¾ããï¼ IMAPã®ããã°ã­ã°ãæå¹ã«ãããIMAPéä¿¡ã®å¨ã¦ãè¨é²ããã¾ã - æ³¨æï¼"php" ãã©ã¤ãã¼ã¯ãã®æ©è½ããµãã¼ããã¾ããã"STDOUT"  ãã©ã¤ãã¼ã§ã¯è¨é²ãç»é¢ã«è¡¨ããã¾ããHorde ã§ã¯ horde-php ã roundcube ãã©ã¤ãã¼ãä½¿ç¨ããã°è¨é²ã®åºååãæå®ã§ãã¾ãã IMAP ãã¼ã¿ã®ã­ã£ãã·ã¥ãç¡å¹ã«ãã¾ãã è¨­å®ãã¡ã¤ã«ã®ãã¹ã§ããã³ãã³ãè¡ãã©ã¡ã¼ã¿ã¯è¨­å®ãã¡ã¤ã«ã®å¤ãä¸æ¸ããã¾ãã å¦çã«è¦ããæéãè¨æ¸¬ãã¾ããã§ããã ãè¨é²ããããã«ããã¹ãã§ãã ã¡ã¢ãªã®ä½¿ç¨ç¶æ³ã§ããã§ããã ãè¨é²ããããã«ããã¹ãã§ãã æ¥ç¶ã®ç¨®é¡ãæå®ãã¦ä¸ãããÂ¥"tlsÂ¥" ã Â¥"sslÂ¥" ã§ãã ä½¿ç¨ãã¹ã Kolab ããã¯ã¨ã³ããã©ã¤ãã¼
ä»¥ä¸ããé¸æ:

 - horde     [IMAP]: PHPã§æ¸ããã Horde_Imap_Client ãã©ã¤ãã¼
 - horde-php [IMAP]: PHP ã® c-client ãä½¿ç¨ãã Horde_Imap_Client ãã©ã¤ãã¼
 - php       [IMAP]: c-client ãåã«ãã PHP ã® imap_* é¢æ°
 - pear      [IMAP]: PEAR-Net_IMAP ãã©ã¤ãã¼
 - roundcube [IMAP]: roundcube IMAP ãã©ã¤ãã¼
 - mock      [Mem.]: ã¡ã¢ãªã¼ãä½¿ç¨ããããã¼ã®ãã©ã¤ãã¼ ãã¼ã¿ãä¿æãã¦ãããã¹ãã§ãã ã¦ã¼ã¶ãããã¯ã¨ã³ãã¸ã®ã¢ã¯ã»ã¹ã«ä½¿ç¨ãããã¹ã¯ã¼ãã§ãã ãã¹ãã¸ã®æ¥ç¶ã«ä½¿ç¨ãã¹ããã¼ãã§ãã ã¦ã¼ã¶ã¯ããã¯ã¨ã³ãã«ã¢ã¯ã»ã¹ãã¦ãã¾ãã æå®ããã LOG å ´æã«ã­ã°ãæ¸ãåºãã¾ããÂ¥"STDOUTÂ¥" ãæå®ããã¨ã­ã°ãç´æ¥ç»é¢ã«åºåãã¾ãã [ãªãã·ã§ã³] ã¢ã¸ã¥ã¼ã«åä½

å¯è½ãªã¢ã¸ã¥ã¼ã«åä½:

 