<?php
include 'config.php';
// Dummy data for collections
$collections = [
    [
        'title' => 'The Great Gatsby',
        'author' => 'F. Scott Fitzgerald',
        'year' => 1925,
        'genre' => 'Classic',
        'cover' => 'https://covers.openlibrary.org/b/id/7222246-L.jpg',
        'desc' => 'A novel set in the Roaring Twenties, exploring themes of wealth, love, and the American Dream.'
    ],
    [
        'title' => 'To Kill a Mockingbird',
        'author' => 'Harper Lee',
        'year' => 1960,
        'genre' => 'Classic',
        'cover' => 'https://covers.openlibrary.org/b/id/8228691-L.jpg',
        'desc' => 'A story of racial injustice and childhood innocence in the Deep South.'
    ],
    [
        'title' => '1984',
        'author' => 'George Orwell',
        'year' => 1949,
        'genre' => 'Dystopian',
        'cover' => 'https://covers.openlibrary.org/b/id/7222246-L.jpg',
        'desc' => 'A chilling prophecy about the future and a warning against totalitarianism.'
    ],
    [
        'title' => 'The Hobbit',
        'author' => 'J.R.R. Tolkien',
        'year' => 1937,
        'genre' => 'Fantasy',
        'cover' => 'https://covers.openlibrary.org/b/id/6979861-L.jpg',
        'desc' => 'A fantasy adventure that sets the stage for the Lord of the Rings trilogy.'
    ],
    [
        'title' => 'Pride and Prejudice',
        'author' => 'Jane Austen',
        'year' => 1813,
        'genre' => 'Romance',
        'cover' => 'https://covers.openlibrary.org/b/id/8091016-L.jpg',
        'desc' => 'A classic tale of love and misunderstanding in 19th-century England.'
    ],
];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Library Collection</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background: #f7f8fa;
            margin: 0;
            padding: 0;
        }

        .collection-header {
            text-align: center;
            padding: 40px 0 20px 0;
            background: linear-gradient(90deg, #6a11cb 0%, #2575fc 100%);
            color: #fff;
            margin-bottom: 30px;
        }

        .collection-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 30px;
            max-width: 1200px;
            margin: 0 auto 40px auto;
        }

        .collection-card {
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 4px 24px rgba(80, 80, 120, 0.08);
            width: 260px;
            transition: transform 0.2s, box-shadow 0.2s;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .collection-card:hover {
            transform: translateY(-8px) scale(1.03);
            box-shadow: 0 8px 32px rgba(80, 80, 120, 0.16);
        }

        .collection-cover {
            width: 100%;
            height: 340px;
            object-fit: cover;
            background: #eaeaea;
        }

        .collection-content {
            padding: 18px 20px 20px 20px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .collection-title {
            font-size: 1.18rem;
            font-weight: 600;
            margin: 0 0 8px 0;
            color: #22223b;
        }

        .collection-meta {
            font-size: 0.95rem;
            color: #6c757d;
            margin-bottom: 10px;
        }

        .collection-desc {
            font-size: 0.97rem;
            color: #444;
            margin-bottom: 12px;
            flex: 1;
        }

        .collection-genre {
            display: inline-block;
            background: #e0e7ff;
            color: #3b3b6d;
            font-size: 0.85rem;
            padding: 4px 12px;
            border-radius: 12px;
            margin-top: 6px;
            align-self: flex-start;
        }

        @media (max-width: 900px) {
            .collection-container {
                gap: 20px;
            }

            .collection-card {
                width: 45vw;
                min-width: 220px;
                max-width: 320px;
            }
        }

        @media (max-width: 600px) {
            .collection-container {
                flex-direction: column;
                align-items: center;
            }

            .collection-card {
                width: 90vw;
                min-width: 0;
            }
        }
    </style>

</head>

<body>
    <?php include 'views/header_index.php'; ?>
    <?php include 'views/navbar_index.php'; ?>

    <div class="collection-header" style="position: relative; margin-top: 80px;">
        <h1 style="font-size:2.5rem; font-weight:700; letter-spacing:1px; margin-bottom:10px; text-shadow:0 2px 12px rgba(40,40,80,0.10);">
            <i class="fa-solid fa-book-open-reader" style="margin-right:12px; color:#ffd700;"></i>
            Library Collection
        </h1>
        <p style="font-size:1.15rem; color:#e0e7ff; margin-bottom:18px; max-width:500px; margin-left:auto; margin-right:auto;">
            Explore our <span style="color:#ffd700; font-weight:500;">curated selection</span> of books across genres and eras.
        </p>
        <div style="position:absolute; left:50%; bottom:-18px; transform:translateX(-50%);">
            <span style="display:inline-block; width:60px; height:6px; border-radius:6px; background:linear-gradient(90deg,#ffd700,#6a11cb); opacity:0.7;"></span>
        </div>
    </div>
    <div class="collection-container">
        <?php foreach ($collections as $book): ?>
            <div class="collection-card">
                <img class="collection-cover" src="<?= htmlspecialchars($book['cover']) ?>" alt="<?= htmlspecialchars($book['title']) ?>">
                <div class="collection-content">
                    <div class="collection-title"><?= htmlspecialchars($book['title']) ?></div>
                    <div class="collection-meta">
                        <?= htmlspecialchars($book['author']) ?> &middot; <?= htmlspecialchars($book['year']) ?>
                    </div>
                    <div class="collection-desc"><?= htmlspecialchars($book['desc']) ?></div>
                    <span class="collection-genre"><?= htmlspecialchars($book['genre']) ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php include 'views/footer_index.php'; ?>

</body>

</html>