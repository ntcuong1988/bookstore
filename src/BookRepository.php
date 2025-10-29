<?php
namespace App;
class BookRepository {
  private \PDO $pdo;
  public function __construct(\PDO $pdo){ $this->pdo=$pdo; }
  public function all(int $offset=0,int $limit=20,?string $q=null): array {
    if($q){
      $stmt=$this->pdo->prepare("SELECT id,sku,title,author,price,stock FROM books WHERE title LIKE ? OR author LIKE ? OR sku LIKE ? ORDER BY id ASC LIMIT ? OFFSET ?");
      $like='%'.$q.'%'; $stmt->bindValue(1,$like); $stmt->bindValue(2,$like); $stmt->bindValue(3,$like);
      $stmt->bindValue(4,$limit,\PDO::PARAM_INT); $stmt->bindValue(5,$offset,\PDO::PARAM_INT); $stmt->execute();
    } else {
      $stmt=$this->pdo->prepare("SELECT id,sku,title,author,price,stock FROM books ORDER BY id ASC LIMIT ? OFFSET ?");
      $stmt->bindValue(1,$limit,\PDO::PARAM_INT); $stmt->bindValue(2,$offset,\PDO::PARAM_INT); $stmt->execute();
    }
    return $stmt->fetchAll();
  }
  public function countAll(?string $q=null): int {
    if($q){ $s=$this->pdo->prepare("SELECT COUNT(*) c FROM books WHERE title LIKE ? OR author LIKE ? OR sku LIKE ?");
            $like='%'.$q.'%'; $s->execute([$like,$like,$like]); }
    else { $s=$this->pdo->query("SELECT COUNT(*) c FROM books"); }
    return (int)($s->fetch()['c']??0);
  }
  public function find(int $id): ?array { $s=$this->pdo->prepare("SELECT id,sku,title,author,price,stock,description FROM books WHERE id=?"); $s->execute([$id]); $r=$s->fetch(); return $r?:null; }
  public function findBySku(string $sku): ?array { $s=$this->pdo->prepare("SELECT id,sku,title,author,price,stock,description FROM books WHERE sku=?"); $s->execute([$sku]); $r=$s->fetch(); return $r?:null; }
  public function create(string $title,string $author,float $price,string $description,?string $sku=null,int $stock=999): int {
    $s=$this->pdo->prepare("INSERT INTO books(sku,title,author,price,stock,description) VALUES (?,?,?,?,?,?)"); $s->execute([$sku,$title,$author,$price,$stock,$description]); return (int)$this->pdo->lastInsertId();
  }
  public function update(int $id,string $title,string $author,float $price,string $description,?string $sku=null,int $stock=999): bool {
    $s=$this->pdo->prepare("UPDATE books SET sku=?,title=?,author=?,price=?,stock=?,description=? WHERE id=?"); return $s->execute([$sku,$title,$author,$price,$stock,$description,$id]);
  }
  public function delete(int $id): bool { $s=$this->pdo->prepare("DELETE FROM books WHERE id=?"); return $s->execute([$id]); }
}
