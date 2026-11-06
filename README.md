# Maestroy FYP Studio

A no-database, multi-page PHP website for students to explore final-year project ideas, prepare proposal drafts, and create a Canva-ready presentation outline.

## Stack
- PHP 8.1+
- Bootstrap 5
- jQuery
- HTML, CSS, JavaScript
- Browser localStorage for the current working project
- Optional OpenAI-compatible API configured on the server

## Pages
- `index.php` — home
- `ideas.php` — project idea discovery
- `workspace.php` — selected project and proposal draft
- `presentation.php` — slide outline and Canva handoff
- `about.php` — platform information

## AI configuration
Copy `includes/config.example.php` to `includes/config.local.php` on the server and enter your private AI API key. The local configuration file is excluded from Git. Without an API key, the idea generator returns clearly labelled demonstration suggestions.

## Deployment
GitHub Actions deploys the `main` branch to InfinityFree using repository secrets `FTP_USERNAME` and `FTP_PASSWORD`. The workflow currently assumes the InfinityFree FTP host is `ftpupload.net` and the destination is `/htdocs/`. Verify these match the FTP account for maestroy.kesug.com before relying on deployment.

## Storage and privacy
No student accounts or database are used. A selected project is stored in that browser's localStorage. It is not synchronized between devices and may be cleared by the browser. Students should review all generated content, verify references, and follow their institution's academic integrity rules.

## Canva
The presentation page prepares a slide outline and opens Canva presentations. This is a Canva handoff, not an authenticated Canva Connect API integration.
