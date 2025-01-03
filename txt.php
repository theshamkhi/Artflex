To display all the articles in your database, you can create a loop that fetches and iterates over all the articles, then displays them in a suitable format.

Here’s how you can do this:

### 1. **Create a method to retrieve all articles:**
First, add a method to the `User` class (or a separate class if you prefer) that fetches all the articles from the database.

```php
class User {
    // Other methods...

    public function getAllArticles() {
        try {
            $query = "SELECT * FROM Articles";
            $stmt = $this->connection->prepare($query);
            $stmt->execute();
            $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $articles;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return [];
        }
    }
}
```

This method will return an array of articles from the `Articles` table.

### 2. **Display the articles in a loop:**
Now, on the page where you want to display the articles, you can loop through the result set and display each article.

```php
// Start the session
session_start();

$user = new User();

// Get all articles
$articles = $user->getAllArticles();

// Check if there are any articles to display
if (!empty($articles)) {
    foreach ($articles as $article) {
        echo "<div class='article'>";
        echo "<h2>" . htmlspecialchars($article['Title']) . "</h2>";
        echo "<p><strong>Category:</strong> " . htmlspecialchars($article['CatID']) . "</p>";
        echo "<p><strong>Published on:</strong> " . htmlspecialchars($article['PubDate']) . "</p>";
        echo "<p><strong>Content:</strong> " . nl2br(htmlspecialchars($article['Content'])) . "</p>";
        echo "<p><strong>Status:</strong> " . htmlspecialchars($article['Status']) . "</p>";
        echo "<p><a href='article.php?id=" . $article['ArticleID'] . "'>Read more</a></p>";
        echo "</div>";
    }
} else {
    echo "<p>No articles available.</p>";
}
```

### Explanation:
- The `getAllArticles()` method fetches all the articles from the database.
- The `foreach` loop goes through each article and displays its details (like title, content, publication date, etc.).
- I used `htmlspecialchars()` to prevent XSS (cross-site scripting) attacks by escaping special characters.
- `nl2br()` is used to convert new lines into `<br>` tags for proper formatting of content.
- The `article.php?id=` link is used to redirect to the individual article page if you want to display full details of a selected article.

### Example Output:
Here’s how the articles will be displayed on the page:

```html
<div class="article">
    <h2>Article Title</h2>
    <p><strong>Category:</strong> Technology</p>
    <p><strong>Published on:</strong> 2025-01-03 10:00:00</p>
    <p><strong>Content:</strong> Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
    <p><strong>Status:</strong> Pending</p>
    <p><a href="article.php?id=1">Read more</a></p>
</div>
```

### Notes:
- You may need to adjust the article display according to your UI design.
- Ensure that the `ArticleID` is a column in the `Articles` table, so you can link to individual articles.