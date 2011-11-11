<?php if (Horde_Util::nonInputVar('sidebarLoaded')): ?>
 </div>
</div>
<?php endif; ?>
<?php if (!$GLOBALS['browser']->isMobile()): ?>
<?php $GLOBALS['notification']->notify(array('listeners' => array('audio'))); ?>
<?php endif; ?>
</body>
</html>
