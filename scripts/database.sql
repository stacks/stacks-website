CREATE TABLE "comments" ("id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL UNIQUE , "tag" VARCHAR NOT NULL, "author" VARCHAR, "site" VARCHAR, "date" DATETIME DEFAULT CURRENT_TIMESTAMP, "comment" TEXT, "email" VARCHAR)
CREATE TABLE "sections" ("number" VARCHAR PRIMARY KEY  NOT NULL ,"title" VARCHAR,"filename" VARCHAR NOT NULL )
CREATE TABLE "tags" ("tag" VARCHAR PRIMARY KEY  NOT NULL ,"label" VARCHAR,"file" VARCHAR,"chapter_page" INTEGER,"book_page" INTEGER,"type" VARCHAR,"book_id" VARCHAR,"value" TEXT, "active" BOOL NOT NULL  DEFAULT TRUE, "name" VARCHAR, "position" INTEGER)
CREATE VIRTUAL TABLE "tags_search" USING fts3(tag, text, text_without_proofs)
