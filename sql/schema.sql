CREATE TABLE user (
  id integer PRIMARY KEY AUTOINCREMENT,
  username text NOT NULL,
  token text
);

CREATE TABLE file (
  id integer PRIMARY KEY AUTOINCREMENT,
  filepath text UNIQUE,
  owner integer,
  FOREIGN KEY(owner) REFERENCES user(id)
);

CREATE TABLE permission (
  file integer,
  user integer,
  read integer,
  write integer,
  FOREIGN KEY(file) REFERENCES file(id),
  FOREIGN KEY(user) REFERENCES user(id),
  PRIMARY KEY(file, user)
);

CREATE TABLE content (
  id integer PRIMARY KEY AUTOINCREMENT,
  file integer,
  creator integer,
  updated integer,
  etag integer,
  content text,
  FOREIGN KEY(file) REFERENCES file(id),
  FOREIGN KEY(creator) REFERENCES user(id)
);
