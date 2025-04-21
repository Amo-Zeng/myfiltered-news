# myfiltered-news
# Filtered.News - 个性化新闻聚合平台

![MyFiltered.News Logo](https://via.placeholder.com/150x50?text=Filtered.News)

## 项目简介

MyFiltered.News 是一个简单而强大的个性化新闻聚合平台，允许用户根据自己的兴趣和偏好自定义新闻体验。该平台支持多种新闻源，包括国际新闻、中文新闻、科技新闻和学术新闻，并提供多种阅读模式以适应不同的阅读习惯。

## 主要特点

- **多样化新闻源**：预设多种新闻源，包括国际、中文、科技和arXiv学术新闻
- **个性化定制**：用户可以选择感兴趣的新闻源，添加自定义RSS源
- **多种布局选项**：
  - 列表视图：传统新闻列表
  - 卡片视图：基于网格的视觉布局
  - 书籍视图：优化的阅读体验
- **阅读模式**：内置阅读视图，无需离开网站即可阅读完整文章
- **主题切换**：支持亮色/暗色主题
- **字体大小调整**：适应不同用户的阅读需求
- **移动设备友好**：响应式设计，在手机和平板上有良好体验

## 技术实现

- 单文件PHP应用，易于部署和维护
- 使用会话(Session)存储用户偏好设置
- RSS解析和内容提取
- 响应式前端设计

## 安装说明

1. 确保您的服务器支持PHP（推荐PHP 7.0+）
2. 将`index.php`文件上传到您的网站根目录
3. 访问您的网站即可开始使用

## 自定义开发

### 添加新的默认RSS源

编辑`index.php`文件中的`$availableRssSources`数组，按照以下格式添加新的RSS源：

```php
'source_key' => [
    'url' => 'https://example.com/rss',
    'name' => '源名称',
    'category' => '分类'  // 'international', 'chinese', 'tech', 'arxiv' 或自定义分类
]
```

### 修改默认设置

编辑`index.php`文件中的初始化部分，可以修改默认布局、主题和默认选中的新闻源。

## 使用指南

1. **浏览新闻**：首次访问时，系统会显示默认选择的新闻源内容
2. **自定义设置**：点击"自定义您的新闻"按钮展开设置面板
3. **选择新闻源**：勾选您感兴趣的新闻源，点击"更新新闻源"按钮应用更改
4. **添加自定义RSS**：在相应表单中输入RSS源名称和URL，点击"添加RSS源"
5. **更改布局**：从下拉菜单中选择您喜欢的布局样式
6. **阅读文章**：点击"阅读更多"或"继续阅读"按钮在内置阅读视图中查看完整文章

## 贡献指南

欢迎对MyFiltered.News项目做出贡献！您可以通过以下方式参与：

1. 提交Bug报告或功能请求
2. 提交代码改进或新功能的Pull Request
3. 改进文档或添加翻译

## 许可证

本项目采用MIT许可证。详情请参阅[LICENSE](LICENSE)文件。

## 联系方式

- 网站：[www.myfiltered.news](https://www.filtered.news)
- 电子邮件：[contact@filtered.news](mailto:contact@filtered.news)
- GitHub：[github.com/yourusername/filtered-news](https://github.com/amozeng/myfiltered-news)

---

*Filtered.News - 让新闻阅读回归本质*
