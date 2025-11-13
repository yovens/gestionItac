
mysql> desc classes;
+-------+--------------+------+-----+---------+----------------+
| Field | Type         | Null | Key | Default | Extra          |
+-------+--------------+------+-----+---------+----------------+
| id    | int          | NO   | PRI | NULL    | auto_increment |
| nom   | varchar(100) | YES  |     | NULL    |                |
| annee | int          | YES  |     | NULL    |                |
+-------+--------------+------+-----+---------+----------------+
3 rows in set (0.56 sec)

mysql> desc etudiants_classes;
+------------------+-------------+------+-----+---------+----------------+
| Field            | Type        | Null | Key | Default | Extra          |
+------------------+-------------+------+-----+---------+----------------+
| id               | int         | NO   | PRI | NULL    | auto_increment |
| etudiant_id      | int         | YES  | MUL | NULL    |                |
| classe_id        | int         | YES  | MUL | NULL    |                |
| annee_academique | varchar(20) | YES  |     | NULL    |                |
+------------------+-------------+------+-----+---------+----------------+
4 rows in set (0.01 sec)

mysql> desc prof;

mysql> desc affectations;
+-------------+-------------+------+-----+---------+----------------+
| Field       | Type        | Null | Key | Default | Extra          |
+-------------+-------------+------+-----+---------+----------------+
| id          | int         | NO   | PRI | NULL    | auto_increment |
| prof_id     | int         | YES  | MUL | NULL    |                |
| matiere_id  | int         | YES  | MUL | NULL    |                |
| classe_id   | int         | YES  | MUL | NULL    |                |
| session_nom | varchar(50) | YES  |     | NULL    |                |
+-------------+-------------+------+-----+---------+----------------+
5 rows in set (0.20 sec)

mysql> desc matieres;
+-------------+--------------+------+-----+---------+----------------+
| Field       | Type         | Null | Key | Default | Extra          |
+-------------+--------------+------+-----+---------+----------------+
| id          | int          | NO   | PRI | NULL    | auto_increment |
| nom         | varchar(150) | YES  |     | NULL    |                |
| code        | varchar(50)  | YES  |     | NULL    |                |
| coefficient | int          | YES  |     | 1       |                |
+-------------+--------------+------+-----+---------+----------------+
4 rows in set (0.07 sec)


Query OK, 0 rows affected (8.87 sec)

mysql> desc users;
+------------+--------------+------+-----+-------------------+-------------------+
| Field      | Type         | Null | Key | Default           | Extra             |
+------------+--------------+------+-----+-------------------+-------------------+
| id         | int          | NO   | PRI | NULL              | auto_increment    |
| role_id    | int          | NO   | MUL | NULL              |                   |
| nom        | varchar(100) | YES  |     | NULL              |                   |
| prenom     | varchar(100) | YES  |     | NULL              |                   |
| phone      | varchar(30)  | YES  |     | NULL              |                   |
| email      | varchar(150) | YES  |     | NULL              |                   |
| username   | varchar(100) | YES  | UNI | NULL              |                   |
| password   | varchar(255) | YES  |     | NULL              |                   |
| photo      | varchar(255) | YES  |     | NULL              |                   |
| created_at | timestamp    | YES  |     | CURRENT_TIMESTAMP | DEFAULT_GENERATED |
+------------+--------------+------+-----+-------------------+-------------------+
10 rows in set (0.23 sec)

mysql> desc examens_prof;
+------------+--------------+------+-----+---------+----------------+
| Field      | Type         | Null | Key | Default | Extra          |
+------------+--------------+------+-----+---------+----------------+
| id         | int          | NO   | PRI | NULL    | auto_increment |
| prof_id    | int          | YES  | MUL | NULL    |                |
| matiere_id | int          | YES  | MUL | NULL    |                |
| nom        | varchar(150) | YES  |     | NULL    |                |
| type_exam  | varchar(50)  | YES  |     | NULL    |                |
| date_exam  | date         | YES  |     | NULL    |                |
| note       | text         | YES  |     | NULL    |                |
| fichier    | varchar(255) | YES  |     | NULL    |                |
+------------+--------------+------+-----+---------+----------------+
8 rows in set (0.85 sec)

mysql> desc paiments;
ERROR 1146 (42S02): Table 'itac_db.paiments' doesn't exist
mysql> desc  paiements;
+---------------+---------------+------+-----+---------+----------------+
| Field         | Type          | Null | Key | Default | Extra          |
+---------------+---------------+------+-----+---------+----------------+
| id            | int           | NO   | PRI | NULL    | auto_increment |
| etudiant_id   | int           | YES  | MUL | NULL    |                |
| montant       | decimal(10,2) | YES  |     | NULL    |                |
| versement_num | int           | YES  |     | NULL    |                |
| date_payment  | date          | YES  |     | NULL    |                |
| method        | varchar(100)  | YES  |     | NULL    |                |
| note          | varchar(255)  | YES  |     | NULL    |                |
| receipt       | varchar(255)  | YES  |     | NULL    |                |
+---------------+---------------+------+-----+---------+----------------+
8 rows in set (0.13 sec)

mysql> desc examens;
+-------------+--------------+------+-----+---------+----------------+
| Field       | Type         | Null | Key | Default | Extra          |
+-------------+--------------+------+-----+---------+----------------+
| id          | int          | NO   | PRI | NULL    | auto_increment |
| nom         | varchar(100) | YES  |     | NULL    |                |
| type_exam   | varchar(50)  | YES  |     | NULL    |                |
| date_debut  | date         | YES  |     | NULL    |                |
| date_fin    | date         | YES  |     | NULL    |                |
| session_nom | varchar(50)  | YES  |     | NULL    |                |
+-------------+--------------+------+-----+---------+----------------+
6 rows in set (0.08 sec)

mysql> desc notes;
+-------------+--------------+------+-----+---------+----------------+
| Field       | Type         | Null | Key | Default | Extra          |
+-------------+--------------+------+-----+---------+----------------+
| id          | int          | NO   | PRI | NULL    | auto_increment |
| etudiant_id | int          | YES  | MUL | NULL    |                |
| matiere_id  | int          | YES  | MUL | NULL    |                |
| exam_id     | int          | YES  | MUL | NULL    |                |
| note        | decimal(5,2) | YES  |     | NULL    |                |
+-------------+--------------+------+-----+---------+----------------+
5 rows in set (1.92 sec)

mysql> select * from notes;
+----+-------------+------------+---------+--------+
| id | etudiant_id | matiere_id | exam_id | note   |
+----+-------------+------------+---------+--------+
|  1 |           8 |         25 |       1 |  70.00 |
|  2 |           8 |          3 |       1 | 100.00 |
+----+-------------+------------+---------+--------+


mysql> desc calendar_events;
+-------------+--------------+------+-----+---------+----------------+
| Field       | Type         | Null | Key | Default | Extra          |
+-------------+--------------+------+-----+---------+----------------+
| id          | int          | NO   | PRI | NULL    | auto_increment |
| titre       | varchar(255) | YES  |     | NULL    |                |
| description | text         | YES  |     | NULL    |                |
| date_debut  | date         | YES  |     | NULL    |                |
| date_fin    | date         | YES  |     | NULL    |                |
| type_event  | varchar(50)  | YES  |     | NULL    |                |
| couleur     | varchar(20)  | YES  |     | NULL    |                |
+-------------+--------------+------+-----+---------+----------------+
7 rows in set (0.87 sec)

mysql> desc roles;
+-------+-------------+------+-----+---------+----------------+
| Field | Type        | Null | Key | Default | Extra          |
+-------+-------------+------+-----+---------+----------------+
| id    | int         | NO   | PRI | NULL    | auto_increment |
| name  | varchar(50) | NO   |     | NULL    |                |
+-------+-------------+------+-----+---------+----------------+


CREATE TABLE examens_prof ( -> id INT AUTO_INCREMENT PRIMARY KEY, -> prof_id INT, -> matiere_id INT, -> nom VARCHAR(150), -> type_exam VARCHAR(50), -> date_exam DATE, -> note TEXT, -> fichier VARCHAR(255), -> FOREIGN KEY (prof_id) REFERENCES users(id), -> FOREIGN KEY (matiere_id) REFERENCES matieres(id) -> );