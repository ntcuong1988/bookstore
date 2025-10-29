<?php
namespace App;
class Paginator {
  public static function meta(int $total,int $page,int $perPage): array {
    $pages = max(1, (int)ceil($total / max(1,$perPage)));
    $page = max(1, min($page, $pages));
    $offset = ($page - 1) * $perPage;
    return ['pages'=>$pages,'page'=>$page,'perPage'=>$perPage,'offset'=>$offset];
  }
}
