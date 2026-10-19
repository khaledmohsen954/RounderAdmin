<?php
// واجهات الموبايل تقبل Bearer tokens فقط؛ جلسة مدير المتصفح لا تمنح حساب تطبيق.
return ['guard'=>[], 'stateful'=>[], 'expiration'=>null, 'token_prefix'=>''];
