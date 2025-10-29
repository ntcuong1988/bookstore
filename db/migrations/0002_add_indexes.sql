-- description: Add common indexes (title, author, relations)
-- Add helpful indexes
CREATE INDEX idx_books_title ON books(title);
CREATE INDEX idx_books_author ON books(author);