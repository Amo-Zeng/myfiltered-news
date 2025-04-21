<?php
// Start session to store user preferences
session_start();

// 预定义RSS源列表
$availableRssSources = [
    // 国际新闻
    'nyt_world' => [
        'url' => 'https://rss.nytimes.com/services/xml/rss/nyt/World.xml',
        'name' => 'New York Times - World News',
        'category' => 'international'
    ],
    'bbc_world' => [
        'url' => 'https://feeds.bbci.co.uk/news/world/rss.xml',
        'name' => 'BBC World News',
        'category' => 'international'
    ],
    'guardian_world' => [
        'url' => 'https://www.theguardian.com/world/rss',
        'name' => 'The Guardian - World News',
        'category' => 'international'
    ],
    
    // 中文新闻
    'sina_news' => [
        'url' => 'https://rss.sina.com.cn/news/marquee/ddt.xml',
        'name' => '新浪新闻',
        'category' => 'chinese'
    ],
    'zhihu_daily' => [
        'url' => 'https://www.zhihu.com/rss',
        'name' => '知乎每日精选',
        'category' => 'chinese'
    ],
    'ifeng_news' => [
        'url' => 'https://plink.anyfeeder.com/weixin/phoenixweekly',
        'name' => '凤凰资讯',
        'category' => 'chinese'
    ],
    'cnbeta' => [
        'url' => 'https://www.cnbeta.com/backend.php',
        'name' => 'CNBeta中文业界资讯',
        'category' => 'chinese'
    ],
    
    // 科技新闻
    'techcrunch' => [
        'url' => 'https://sspai.com/feed',
        'name' => '少数派',
        'category' => 'tech'
    ],
    'wired' => [
        'url' => 'https://www.wired.com/feed/rss',
        'name' => 'Wired',
        'category' => 'tech'
    ],
    'ars_technica' => [
        'url' => 'https://feeds.arstechnica.com/arstechnica/index',
        'name' => 'Ars Technica',
        'category' => 'tech'
    ],
    'engadget' => [
        'url' => 'https://www.engadget.com/rss.xml',
        'name' => 'Engadget',
        'category' => 'tech'
    ],
    'the_verge' => [
        'url' => 'https://www.theverge.com/rss/index.xml',
        'name' => 'The Verge',
        'category' => 'tech'
    ],
    
    // Arxiv学术新闻
    'arxiv_cs' => [
        'url' => 'http://arxiv.org/rss/cs',
        'name' => 'arXiv Computer Science',
        'category' => 'arxiv'
    ],
    'arxiv_ai' => [
        'url' => 'http://arxiv.org/rss/cs.AI',
        'name' => 'arXiv Artificial Intelligence',
        'category' => 'arxiv'
    ],
    'arxiv_ml' => [
        'url' => 'http://arxiv.org/rss/cs.LG',
        'name' => 'arXiv Machine Learning',
        'category' => 'arxiv'
    ],
    'arxiv_physics' => [
        'url' => 'http://arxiv.org/rss/physics',
        'name' => 'arXiv Physics',
        'category' => 'arxiv'
    ]
];
// Initialize default settings if not set
if (!isset($_SESSION['layout'])) {
    $_SESSION['layout'] = 'list'; // Default layout: list, cards, book
}
// 初始化默认选择的RSS源
if (!isset($_SESSION['selected_sources'])) {
    $_SESSION['selected_sources'] = [
        'bbc_world' => true,      // 默认选中的国际新闻
        'techcrunch' => true,         // 默认选中的中文新闻
        'arxiv_physics' => true        // 默认选中的arxiv新闻
    ];
}
if (!isset($_SESSION['theme'])) {
    $_SESSION['theme'] = 'light'; // Default theme: light, dark
}
if (!isset($_SESSION['fontSize'])) {
    $_SESSION['fontSize'] = 'medium'; // Default font size: small, medium, large
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Layout change
    if (isset($_POST['layout'])) {
        $_SESSION['layout'] = $_POST['layout'];
    }
    
    // Theme change
    if (isset($_POST['theme'])) {
        $_SESSION['theme'] = $_POST['theme'];
    }
    
    // Font size change
    if (isset($_POST['fontSize'])) {
        $_SESSION['fontSize'] = $_POST['fontSize'];
    }
    
      // 处理RSS源的选择
    if (isset($_POST['update_sources'])) {
        // 重置所有源
        $_SESSION['selected_sources'] = [];
        
        // 更新选中的源
        foreach ($availableRssSources as $key => $source) {
            if (isset($_POST['source_' . $key])) {
                $_SESSION['selected_sources'][$key] = true;
            }
        }
    }
    
    // 添加自定义RSS源
    if (isset($_POST['newRssUrl']) && !empty($_POST['newRssUrl']) && 
        isset($_POST['newRssName']) && !empty($_POST['newRssName'])) {
        
        $customKey = 'custom_' . md5($_POST['newRssUrl']);
        $availableRssSources[$customKey] = [
            'url' => $_POST['newRssUrl'],
            'name' => $_POST['newRssName'],
            'category' => 'custom'
        ];
        
        $_SESSION['selected_sources'][$customKey] = true;
        
        // 保存自定义源到会话
        if (!isset($_SESSION['custom_sources'])) {
            $_SESSION['custom_sources'] = [];
        }
        $_SESSION['custom_sources'][$customKey] = [
            'url' => $_POST['newRssUrl'],
            'name' => $_POST['newRssName'],
            'category' => 'custom'
        ];
    }
    
    // 删除自定义源
    if (isset($_POST['remove_source']) && !empty($_POST['remove_source'])) {
        $sourceKey = $_POST['remove_source'];
        unset($_SESSION['selected_sources'][$sourceKey]);
        
        if (isset($_SESSION['custom_sources'][$sourceKey])) {
            unset($_SESSION['custom_sources'][$sourceKey]);
        }
    }
}
// 恢复自定义源
if (isset($_SESSION['custom_sources']) && is_array($_SESSION['custom_sources'])) {
    foreach ($_SESSION['custom_sources'] as $key => $source) {
        $availableRssSources[$key] = $source;
    }
}
// Check if we're in reading mode
$readingMode = false;
$currentArticle = null;
$articleContent = '';

if (isset($_GET['readnews']) && !empty($_GET['readnews'])) {
    $readingMode = true;
    $articleUrl = base64_decode($_GET['readnews']);
    
    // Fetch the article content
    $articleContent = @file_get_contents($articleUrl);
    
    // If we couldn't fetch the content, try to find it in our cached news
    if ($articleContent === false) {
        // Fetch all feeds to find the article
        $allNews = [];
        foreach ($_SESSION['selected_sources'] as $sourceUrl) {
            $feed = fetchRssFeed($sourceUrl);
            foreach ($feed['items'] as $item) {
                $item['source'] = $feed['title'];
                $allNews[] = $item;
                
                // Check if this is the article we're looking for
                if ($item['link'] == $articleUrl) {
                    $currentArticle = $item;
                }
            }
        }
    } else {
        // Try to extract the main content from the HTML
        // This is a simple extraction and might not work for all sites
        // A more robust solution would use a library like Readability
        $dom = new DOMDocument();
        @$dom->loadHTML($articleContent);
        $xpath = new DOMXPath($dom);
        
        // Try to find the article title
        $titleNode = $xpath->query('//h1')->item(0);
        $title = $titleNode ? $titleNode->textContent : '';
        
        // Try to find the article content
        $contentNodes = $xpath->query('//article | //div[@class="content"] | //div[@class="article-content"]');
        if ($contentNodes->length > 0) {
            $contentNode = $contentNodes->item(0);
            $articleContent = $dom->saveHTML($contentNode);
        } else {
            // Fallback to body content
            $bodyNode = $xpath->query('//body')->item(0);
            $articleContent = $bodyNode ? $dom->saveHTML($bodyNode) : 'Could not extract article content.';
        }
        
        $currentArticle = [
            'title' => $title,
            'link' => $articleUrl,
            'content' => $articleContent
        ];
    }
}

// Function to fetch and parse RSS feeds
function fetchRssFeed($url) {
    $content = @file_get_contents($url);
    if ($content === false) {
        return [
            'title' => 'Error loading feed',
            'link' => '#',
            'items' => []
        ];
    }
    
    $xml = simplexml_load_string($content);
    if ($xml === false) {
        return [
            'title' => 'Error parsing feed',
            'link' => '#',
            'items' => []
        ];
    }
    
    $feed = [
        'title' => (string)$xml->channel->title,
        'link' => (string)$xml->channel->link,
        'items' => []
    ];
    
    foreach ($xml->channel->item as $item) {
        $pubDate = isset($item->pubDate) ? strtotime($item->pubDate) : time();
        
        // Extract image if available
        $imageUrl = null;
        if (isset($item->enclosure) && (string)$item->enclosure['type'] == 'image/jpeg') {
            $imageUrl = (string)$item->enclosure['url'];
        } else {
            // Try to extract image from description or content
            $content = isset($item->children('content', true)->encoded) ? 
                       (string)$item->children('content', true)->encoded : 
                       (string)$item->description;
            
            preg_match('/<img.+src=[\'"](?P<src>.+?)[\'"].*>/i', $content, $matches);
            if (isset($matches['src'])) {
                $imageUrl = $matches['src'];
            }
        }
        
        // Clean description
        $description = isset($item->description) ? (string)$item->description : '';
        $description = strip_tags($description);
        if (strlen($description) > 200) {
            $description = substr($description, 0, 197) . '...';
        }
        
        $feed['items'][] = [
            'title' => (string)$item->title,
            'link' => (string)$item->link,
            'description' => $description,
            'pubDate' => $pubDate,
            'image' => $imageUrl
        ];
    }
    
    return $feed;
}

// 获取当前选中的RSS源URL列表
$selectedSourceUrls = [];
foreach ($_SESSION['selected_sources'] as $key => $selected) {
    if ($selected && isset($availableRssSources[$key])) {
        $selectedSourceUrls[] = $availableRssSources[$key]['url'];
    }
}

// 只有在非阅读模式下才获取所有新闻
$allNews = [];
if (!isset($readingMode) || !$readingMode) {
    foreach ($selectedSourceUrls as $sourceUrl) {
        $feed = fetchRssFeed($sourceUrl);
        foreach ($feed['items'] as $item) {
            $item['source'] = $feed['title'];
            $allNews[] = $item;
        }
    }

    // Sort news by publication date (newest first)
    usort($allNews, function($a, $b) {
        return $b['pubDate'] - $a['pubDate'];
    });

    // Limit to 50 items for performance
    $allNews = array_slice($allNews, 0, 50);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $readingMode && $currentArticle ? htmlspecialchars($currentArticle['title']) : 'MyFiltered.News'; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        :root {
            --bg-color: <?php echo $_SESSION['theme'] === 'dark' ? '#121212' : '#f5f5f5'; ?>;
            --text-color: <?php echo $_SESSION['theme'] === 'dark' ? '#e0e0e0' : '#333'; ?>;
            --card-bg: <?php echo $_SESSION['theme'] === 'dark' ? '#1e1e1e' : '#fff'; ?>;
            --border-color: <?php echo $_SESSION['theme'] === 'dark' ? '#333' : '#ddd'; ?>;
            --accent-color: #4a90e2;
            --font-size: <?php 
                echo $_SESSION['fontSize'] === 'small' ? '14px' : 
                    ($_SESSION['fontSize'] === 'large' ? '18px' : '16px'); 
            ?>;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-color);
            font-size: var(--font-size);
            line-height: 1.6;
            transition: background-color 0.3s, color 0.3s;
        }
        
        header {
            background-color: var(--accent-color);
            color: white;
            padding: 1rem;
            text-align: center;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1rem;
        }
        
        .settings-panel {
            background-color: var(--card-bg);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            border: 1px solid var(--border-color);
        }
        
        .settings-toggle {
            background-color: var(--accent-color);
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            cursor: pointer;
            font-size: var(--font-size);
            margin-bottom: 1rem;
            display: block;
            width: 100%;
        }
        
        .settings-content {
            display: none;
        }
        
        .settings-content.active {
            display: block;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }
        
        select, input[type="text"], button {
            width: 100%;
            padding: 0.5rem;
            border-radius: 4px;
            border: 1px solid var(--border-color);
            background-color: var(--bg-color);
            color: var(--text-color);
            font-size: var(--font-size);
        }
        
        button {
            background-color: var(--accent-color);
            color: white;
            cursor: pointer;
            border: none;
        }
        
        button:hover {
            opacity: 0.9;
        }
        
        .source-list {
            margin-top: 1rem;
        }
        
        .source-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .source-item button {
            width: auto;
            padding: 0.25rem 0.5rem;
            background-color: #e74c3c;
        }
        
        /* News layouts */
        .news-container {
            margin-top: 1rem;
        }
        
        /* List layout */
        .layout-list .news-item {
            background-color: var(--card-bg);
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            border: 1px solid var(--border-color);
        }
        
        .layout-list .news-item h3 {
            margin-bottom: 0.5rem;
        }
        
        .layout-list .news-meta {
            font-size: 0.9em;
            color: #888;
            margin-bottom: 0.5rem;
        }
        
        .layout-list .news-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 4px;
            margin-bottom: 0.5rem;
            display: block;
        }
        
        /* Cards layout */
        .layout-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1rem;
        }
        
        .layout-cards .news-item {
            background-color: var(--card-bg);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            border: 1px solid var(--border-color);
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .layout-cards .news-image-container {
            height: 180px;
            overflow: hidden;
        }
        
        .layout-cards .news-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .layout-cards .news-content {
            padding: 1rem;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        
        .layout-cards .news-title {
            margin-bottom: 0.5rem;
        }
        
        .layout-cards .news-meta {
            font-size: 0.9em;
            color: #888;
            margin-bottom: 0.5rem;
        }
        
        .layout-cards .news-description {
            flex-grow: 1;
        }
        
        /* Book layout */
        .layout-book {
            max-width: 800px;
            margin: 0 auto;
            background-color: var(--card-bg);
            border-radius: 8px;
            padding: 2rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            border: 1px solid var(--border-color);
        }
        
        .layout-book .news-item {
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .layout-book .news-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .layout-book .news-title {
            font-size: 1.5em;
            margin-bottom: 1rem;
        }
        
        .layout-book .news-meta {
            font-size: 0.9em;
            color: #888;
            margin-bottom: 1rem;
        }
        
        .layout-book .news-image {
            width: 100%;
            max-height: 400px;
            object-fit: contain;
            margin: 1rem 0;
            display: block;
        }
        
        .layout-book .news-description {
            line-height: 1.8;
            margin-bottom: 1rem;
        }
        
        .read-more {
            display: inline-block;
            background-color: var(--accent-color);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            text-decoration: none;
            margin-top: 0.5rem;
        }
        
        .read-more:hover {
            opacity: 0.9;
        }
        
        @media (max-width: 768px) {
            .layout-cards {
                grid-template-columns: 1fr;
            }
            
            .layout-book {
                padding: 1rem;
            }
        }
       /* Reading mode styles */
        .reading-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
            background-color: var(--card-bg);
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            line-height: 1.8;
        }
        
        .reading-container h1 {
            font-size: 2em;
            margin-bottom: 1rem;
        }
        
        .reading-container img {
            max-width: 100%;
            height: auto;
            margin: 1rem 0;
        }
        
        .reading-container p {
            margin-bottom: 1rem;
        }
        
        .back-button {
            display: inline-block;
            background-color: var(--accent-color);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 4px;
            text-decoration: none;
            margin-bottom: 1rem;
        }
        
        .back-button:hover {
            opacity: 0.9;
        }
        
        /* 新增RSS源选择样式 */
        .source-categories {
            margin-top: 1rem;
        }
        
        .source-category {
            margin-bottom: 1rem;
        }
        
        .source-category h4 {
            margin-bottom: 0.5rem;
            padding-bottom: 0.25rem;
            border-bottom: 1px solid var(--border-color);
        }
        
        .source-item-checkbox {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        
        .source-item-checkbox input[type="checkbox"] {
            margin-right: 0.5rem;
            width: auto;
        }
        
        .source-item-checkbox label {
            margin-bottom: 0;
            font-weight: normal;
        }
    </style>
</head>
<body>
    <header>
        <h1><?php echo $readingMode ? 'Reading Mode' : 'MyFiltered.News'; ?></h1>
    </header>
    
    <div class="container">
        <?php if ($readingMode): ?>
            <a href="index.php" class="back-button"><i class="fas fa-arrow-left"></i> Back to News</a>
            
            <div class="reading-container">
                <?php if ($currentArticle): ?>
                    <h1><?php echo htmlspecialchars($currentArticle['title'] ?? 'Article Title'); ?></h1>
                    
                    <?php if (isset($currentArticle['content'])): ?>
                        <div class="article-content">
                            <?php echo $currentArticle['content']; ?>
                        </div>
                    <?php else: ?>
                        <p><?php echo htmlspecialchars($currentArticle['description'] ?? ''); ?></p>
                        <p>For the full article, please visit: <a href="<?php echo htmlspecialchars($currentArticle['link']); ?>" target="_blank"><?php echo htmlspecialchars($currentArticle['link']); ?></a></p>
                    <?php endif; ?>
                <?php else: ?>
                    <h1>Article Not Found</h1>
                    <p>Sorry, we couldn't retrieve the article you requested.</p>
                    <a href="index.php" class="back-button">Return to Homepage</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            
        <div class="settings-panel">
            <button class="settings-toggle">
                <i class="fas fa-cog"></i> Customize Your News
            </button>
            
            <div class="settings-content">
                <form method="post" action="">
                    <div class="form-group">
                        <label for="layout">Layout Style:</label>
                        <select name="layout" id="layout">
                            <option value="list" <?php echo $_SESSION['layout'] === 'list' ? 'selected' : ''; ?>>List View</option>
                            <option value="cards" <?php echo $_SESSION['layout'] === 'cards' ? 'selected' : ''; ?>>Card View</option>
                            <option value="book" <?php echo $_SESSION['layout'] === 'book' ? 'selected' : ''; ?>>Book View</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="theme">Theme:</label>
                        <select name="theme" id="theme">
                            <option value="light" <?php echo $_SESSION['theme'] === 'light' ? 'selected' : ''; ?>>Light</option>
                            <option value="dark" <?php echo $_SESSION['theme'] === 'dark' ? 'selected' : ''; ?>>Dark</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="fontSize">Font Size:</label>
                        <select name="fontSize" id="fontSize">
                            <option value="small" <?php echo $_SESSION['fontSize'] === 'small' ? 'selected' : ''; ?>>Small</option>
                            <option value="medium" <?php echo $_SESSION['fontSize'] === 'medium' ? 'selected' : ''; ?>>Medium</option>
                            <option value="large" <?php echo $_SESSION['fontSize'] === 'large' ? 'selected' : ''; ?>>Large</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit">Apply Settings</button>
                    </div>
                </form>
                
         <form method="post" action="">
         <h3>News Source</h3>
                    
                    <div class="source-categories">
                        <div class="source-category">
                            <h4>Global News</h4>
                            <?php foreach ($availableRssSources as $key => $source): ?>
                                <?php if ($source['category'] === 'international'): ?>
                                    <div class="source-item-checkbox">
                                        <input type="checkbox" id="source_<?php echo $key; ?>" name="source_<?php echo $key; ?>" 
                                            <?php echo isset($_SESSION['selected_sources'][$key]) ? 'checked' : ''; ?>>
                                        <label for="source_<?php echo $key; ?>"><?php echo htmlspecialchars($source['name']); ?></label>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="source-category">
                            <h4>中文新闻</h4>
                            <?php foreach ($availableRssSources as $key => $source): ?>
                                <?php if ($source['category'] === 'chinese'): ?>
                                    <div class="source-item-checkbox">
                                        <input type="checkbox" id="source_<?php echo $key; ?>" name="source_<?php echo $key; ?>" 
                                            <?php echo isset($_SESSION['selected_sources'][$key]) ? 'checked' : ''; ?>>
                                        <label for="source_<?php echo $key; ?>"><?php echo htmlspecialchars($source['name']); ?></label>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="source-category">
                            <h4>Tech News</h4>
                            <?php foreach ($availableRssSources as $key => $source): ?>
                                <?php if ($source['category'] === 'tech'): ?>
                                    <div class="source-item-checkbox">
                                        <input type="checkbox" id="source_<?php echo $key; ?>" name="source_<?php echo $key; ?>" 
                                            <?php echo isset($_SESSION['selected_sources'][$key]) ? 'checked' : ''; ?>>
                                        <label for="source_<?php echo $key; ?>"><?php echo htmlspecialchars($source['name']); ?></label>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="source-category">
                            <h4>ArXiv News</h4>
                            <?php foreach ($availableRssSources as $key => $source): ?>
                                <?php if ($source['category'] === 'arxiv'): ?>
                                    <div class="source-item-checkbox">
                                        <input type="checkbox" id="source_<?php echo $key; ?>" name="source_<?php echo $key; ?>" 
                                            <?php echo isset($_SESSION['selected_sources'][$key]) ? 'checked' : ''; ?>>
                                        <label for="source_<?php echo $key; ?>"><?php echo htmlspecialchars($source['name']); ?></label>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        
                        <?php if (isset($_SESSION['custom_sources']) && !empty($_SESSION['custom_sources'])): ?>
                        <div class="source-category">
                            <h4>DIY News</h4>
                            <?php foreach ($availableRssSources as $key => $source): ?>
                                <?php if ($source['category'] === 'custom'): ?>
                                    <div class="source-item-checkbox">
                                        <input type="checkbox" id="source_<?php echo $key; ?>" name="source_<?php echo $key; ?>" 
                                            <?php echo isset($_SESSION['selected_sources'][$key]) ? 'checked' : ''; ?>>
                                        <label for="source_<?php echo $key; ?>"><?php echo htmlspecialchars($source['name']); ?></label>
                                        <form method="post" action="" style="display: inline; margin-left: 10px;">
                                            <input type="hidden" name="remove_source" value="<?php echo $key; ?>">
                                            <button type="submit" class="small-button">Remove</button>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" name="update_sources">Update News</button>
                    </div>
                </form>
                
                <form method="post" action="">
                    <h3>Add RSS</h3>
                    <div class="form-group">
                        <label for="newRssName">RSS Name:</label>
                        <input type="text" name="newRssName" id="newRssName" placeholder="RSS Name">
                    </div>
                    <div class="form-group">
                        <label for="newRssUrl">RSS URL:</label>
                        <input type="text" name="newRssUrl" id="newRssUrl" placeholder="RSS URL">
                    </div>
                    
                    <div class="form-group">
                        <button type="submit">Add RSS</button>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="news-container layout-<?php echo $_SESSION['layout']; ?>">
            <?php if (empty($allNews)): ?>
                <div class="no-news">
                    <p>No news articles found. Please add some RSS feeds to get started.</p>
                </div>
            <?php else: ?>
                <?php foreach ($allNews as $item): ?>
                    <article class="news-item">
                        <?php if ($_SESSION['layout'] === 'cards'): ?>
                            <?php if ($item['image']): ?>
                                <div class="news-image-container">
                                    <img class="news-image" src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                                </div>
                            <?php endif; ?>
                            <div class="news-content">
                                <h3 class="news-title"><?php echo htmlspecialchars($item['title']); ?></h3>
                                <div class="news-meta">
                                    <span class="news-source"><?php echo htmlspecialchars($item['source']); ?></span> | 
                                    <span class="news-date"><?php echo date('M j, Y', $item['pubDate']); ?></span>
                                </div>
                                <div class="news-description"><?php echo htmlspecialchars($item['description']); ?></div>
<a href="index.php?readnews=<?php echo urlencode(base64_encode($item['link'])); ?>" class="read-more">Read More</a>
                            </div>
                        <?php elseif ($_SESSION['layout'] === 'book'): ?>
                            <h2 class="news-title"><?php echo htmlspecialchars($item['title']); ?></h2>
                            <div class="news-meta">
                                <span class="news-source"><?php echo htmlspecialchars($item['source']); ?></span> | 
                                <span class="news-date"><?php echo date('F j, Y', $item['pubDate']); ?></span>
                            </div>
                            <?php if ($item['image']): ?>
                                <img class="news-image" src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                            <?php endif; ?>
                            <div class="news-description"><?php echo htmlspecialchars($item['description']); ?></div>
<a href="index.php?readnews=<?php echo urlencode(base64_encode($item['link'])); ?>" class="read-more">Continue Reading</a>
                        <?php else: ?>
                            <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                            <div class="news-meta">
                                <span class="news-source"><?php echo htmlspecialchars($item['source']); ?></span> | 
                                <span class="news-date"><?php echo date('M j, Y', $item['pubDate']); ?></span>
                            </div>
                            <?php if ($item['image']): ?>
                                <img class="news-image" src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                            <?php endif; ?>
                            <p><?php echo htmlspecialchars($item['description']); ?></p>
<a href="index.php?readnews=<?php echo urlencode(base64_encode($item['link'])); ?>" class="read-more">Read More</a>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const settingsToggle = document.querySelector('.settings-toggle');
            const settingsContent = document.querySelector('.settings-content');
            
            settingsToggle.addEventListener('click', function() {
                settingsContent.classList.toggle('active');
                
                if (settingsContent.classList.contains('active')) {
                    settingsToggle.innerHTML = '<i class="fas fa-times"></i> Close Settings';
                } else {
                    settingsToggle.innerHTML = '<i class="fas fa-cog"></i> Customize Your News';
                }
            });
        });
    </script>
</body>
</html>
