<?php
print '<span class="badge badge-status'.$object->status.'">'.dol_escape_htmltag($object->getStatusLabel()).'</span>';
