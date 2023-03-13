<!-- PROJECT LOGO -->
<br />
<div align="center">
  <a href="https://git.emundus.io/emundus/saas/app_tchooz" style="display: flex;justify-content: center;align-items: center;gap: 16px">
    <img src="images/custom/logo.png" alt="Core logo" width="400">
  </a>

<h3 align="center">eMundus</h3>

  <p align="center">
    Online application management for Joomla 3.10.x
    <br />
    <a href="https://emundus.atlassian.net/wiki/spaces/HD/overview"><strong>Explore the docs »</strong></a>
    <br />
    <br />
    <a href="https://git.emundus.io/emundus/cms/core/-/issues">Report Bug</a>
    ·
    <a href="https://git.emundus.io/emundus/cms/core/-/issues">Request Feature</a>
  </p>
</div>



<!-- TABLE OF CONTENTS -->
<details>
  <summary>Table of Contents</summary>
  <ol>
    <li>
      <a href="#about-the-project">About The Project</a>
      <ul>
        <li><a href="#built-with">Built With</a></li>
      </ul>
    </li>
    <li>
      <a href="#getting-started">Getting Started</a>
      <ul>
        <li><a href="#prerequisites">Prerequisites</a></li>
        <li><a href="#installation">Installation</a></li>
      </ul>
    </li>
    <li><a href="#roadmap">Roadmap</a></li>
    <li><a href="#acknowledgments">Acknowledgments</a></li>
  </ol>
</details>



<!-- ABOUT THE PROJECT -->
## About The Project


[![Product Name Screen Shot][product-screenshot]](https://demo.tchooz.io)

Manage your application campaigns and calls for proposals simply

* Manage your application programmes, create your application portal and launch your campaign
* Evaluate the content of applications
* Automatic response
* Archive and export campaign data
* Create automatic notifications to your applicants
* Group your files using status and tags
* Manage your user profiles and groups
* Granular rights management
* And much more!

### Built With

[![Joomla][Joomla.com]][Joomla-url]
<br/><br/>
[![Vue][Vue.js]][Vue-url]

<!-- GETTING STARTED -->
## Getting Started


### Prerequisites

#### PHP
[![PHP][PHP-min-badge]][PHP-url]
* MacOS : It's recommended to install PHP with homebrew : `brew install php`. You can switch of versions by adding `@7.x`.
    * If you need more informations : https://daily-dev-tips.com/posts/installing-php-on-your-mac/

#### NodeJS
[![Node][Node-min-badge]][Node-url]
[![Node][Node-reco-badge]][Node-url]

This project is built with VueJS so it is necessary to have NodeJS installed on your computer.
* MacOS : Download Node [here][Node-url] OR if you use homebrew run following command
    * `brew install node`
* Windows : Download Node [here][Node-url]

#### Composer
Joomla requires an installation of composer.
You can install composer only for this project by following this [documentation][Composer-local-installation].

If you need composer for other project you can install it globally by following this [chapter][Composer-global-installation].

<p align="right">(<a href="#readme-top">back to top</a>)</p>

<!-- DEV USAGE -->
### For developers
1. Run Hot Reload for VueJS
   ```sh
   yarn run watch
   ```

<p align="right">(<a href="#readme-top">back to top</a>)</p>

#### Seeders
You can login as sysadmin and go to Components > eMundus > Data samples.
This interface allows you to generate users and application files.

<p align="right">(<a href="#readme-top">back to top</a>)</p>


<!-- ROADMAP -->
## Roadmap

- [ ] Add SSO connection with Keycloak
- [ ] Add evaluation and decision in formbuilder
- [ ] Allow coordinators to create their own workflow

<p align="right">(<a href="#readme-top">back to top</a>)</p>


<!-- ACKNOWLEDGMENTS -->
## Acknowledgments

Below are several links that are essential for developers working on this project.

* [Master Joomla](https://developer.joomla.org/)
* [Vue 3](https://vuejs.org/guide/introduction.html)
* [Material Icons](https://fonts.google.com/icons?icon.set=Material+Icons)

<p align="right">(<a href="#readme-top">back to top</a>)</p>



<!-- MARKDOWN LINKS & IMAGES -->
<!-- https://www.markdownguide.org/basic-syntax/#reference-style-links -->
[product-screenshot]: images/product-screenshot.png
[Vue.js]: https://img.shields.io/badge/Vue.js-35495E?style=for-the-badge&logo=vuedotjs&logoColor=4FC08D
[Vue-url]: https://vuejs.org/
[Joomla.com]: https://img.shields.io/badge/Joomla%203.10.X-5091CD?style=for-the-badge&logo=joomla&logoColor=white
[Joomla-url]: https://www.joomla.fr/
[Node-url]: https://nodejs.org/
[Node-min-badge]: https://img.shields.io/badge/min-16.x-orange
[Node-reco-badge]: https://img.shields.io/badge/recommended-18.x-green
[Composer-local-installation]: https://getcomposer.org/download/
[Composer-global-installation]: https://getcomposer.org/doc/00-intro.md#globally
[PHP-min-badge]: https://img.shields.io/badge/dependencies-PHP%207.4-green
[PHP-url]: https://www.php.net/manual/en/install.macosx.php
[Mailtrap-url]: https://mailtrap.io

