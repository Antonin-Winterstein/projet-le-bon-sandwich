<?php

use lbs\fidelisation\errors\BadUri;

return [
  'notFoundHandler'=> function($c) {
    return function ($rq, $rs) use ($c) {
      return BadUri::error($c, $rq, $rs);
    };
  },

  'notAllowedHandler' => function($c) {
    return function ($rq, $rs, $methods) use ($c) {
      return errors\NotAllowed::error($c, $rq, $rs, $methods);
    };
  },

  // 'phpErrorHandler' => function($c) {
  //   return function ($rq, $rs, $error) use ($c) {
  //     return errors\Internal::error($c, $rq, $rs, $error);
  //   };
  // }
];