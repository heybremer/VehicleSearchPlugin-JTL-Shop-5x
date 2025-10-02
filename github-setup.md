# GitHub Repository Kurulum Rehberi

## 1. GitHub'da Repository Oluşturun

1. **https://github.com** adresine gidin
2. **"New repository"** butonuna tıklayın
3. **Repository name**: `VehicleSearchPlugin-JTL-Shop-5x`
4. **Description**: `Advanced vehicle search plugin for JTL Shop 5.x with manufacturer, model and type selection`
5. **Public** seçin (açık kaynak için)
6. **"Create repository"** butonuna tıklayın

## 2. Repository'yi Yerel Projeye Bağlayın

Aşağıdaki komutları sırasıyla çalıştırın:

```bash
# GitHub repository'nizi remote olarak ekleyin
git remote add origin https://github.com/KULLANICI_ADINIZ/VehicleSearchPlugin-JTL-Shop-5x.git

# Ana branch'i main olarak ayarlayın
git branch -M main

# Repository'yi GitHub'a yükleyin
git push -u origin main
```

**Not**: `KULLANICI_ADINIZ` yerine GitHub kullanıcı adınızı yazın.

## 3. Alternatif: GitHub Desktop Kullanın

1. **GitHub Desktop** uygulamasını indirin
2. **"Clone a repository from the Internet"** seçin
3. **GitHub'da oluşturduğunuz repository'yi seçin**
4. **"Clone"** butonuna tıklayın
5. **Dosyaları kopyalayın** ve **"Commit to main"** yapın
6. **"Push origin"** ile GitHub'a yükleyin

## 4. Manuel Yükleme (En Kolay)

1. **GitHub'da repository oluşturun**
2. **"uploading an existing file"** seçeneğini tıklayın
3. **Bu klasördeki tüm dosyaları seçin**
4. **"Commit changes"** yapın

## 5. Repository Açıklaması

Repository'nize şu README.md içeriğini ekleyin:

```markdown
# Vehicle Search Plugin for JTL Shop 5.x

Advanced vehicle search plugin with manufacturer, model and type selection for JTL Shop 5.x.

## Features
- 🚗 Multi-mode search (Feature-based and Category-based)
- ⚡ AJAX support for dynamic data loading
- 🎨 Responsive design with dark mode support
- 🌍 Multi-language support (Turkish, German, English)
- 📊 Search statistics and analytics
- ⚡ Cache system for performance optimization
- 🔧 Admin panel configuration

## Installation
1. Download the latest release
2. Upload to JTL Shop plugins directory
3. Activate in JTL Shop admin panel
4. Configure settings

## Requirements
- JTL Shop 5.x or higher
- PHP 7.4 or higher
- MySQL 5.7 or higher
```

## 6. Release Oluşturun

1. **"Releases"** sekmesine gidin
2. **"Create a new release"** tıklayın
3. **Tag version**: `v1.0.0`
4. **Release title**: `Vehicle Search Plugin v1.0.0`
5. **Description**: Plugin açıklaması
6. **"Publish release"** tıklayın
