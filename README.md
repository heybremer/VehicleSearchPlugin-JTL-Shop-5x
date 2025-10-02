# Vehicle Search Plugin for JTL Shop 5.x

Gelişmiş araç arama özelliği sunan JTL Shop 5.x eklentisi. Üretici, model ve tip seçimi ile kapsamlı araç arama imkanı sağlar.

## Özellikler

- **Çoklu Arama Modu**: Özellik tabanlı ve kategori tabanlı arama
- **Dinamik Form**: Üretici seçimine göre model ve tip seçenekleri
- **AJAX Desteği**: Sayfa yenilenmeden dinamik veri yükleme
- **Önbellekleme**: Performans optimizasyonu için akıllı önbellekleme
- **İstatistik Takibi**: Arama istatistikleri ve analitik
- **Responsive Tasarım**: Mobil ve masaüstü uyumlu arayüz
- **Çoklu Dil Desteği**: Türkçe, Almanca ve İngilizce dil desteği

## Kurulum

### Gereksinimler

- JTL Shop 5.x veya üzeri
- PHP 7.4 veya üzeri
- MySQL 5.7 veya üzeri

### Kurulum Adımları

1. **Eklentiyi İndirin**
   ```bash
   # Eklenti dosyasını JTL Shop plugins klasörüne kopyalayın
   cp -r VehicleSearchPlugin /path/to/jtl-shop/plugins/
   ```

2. **Dosya İzinlerini Ayarlayın**
   ```bash
   chmod -R 755 /path/to/jtl-shop/plugins/VehicleSearchPlugin
   chown -R www-data:www-data /path/to/jtl-shop/plugins/VehicleSearchPlugin
   ```

3. **JTL Shop Admin Paneline Giriş Yapın**
   - Admin paneline giriş yapın
   - "Eklentiler" bölümüne gidin

4. **Eklentiyi Aktifleştirin**
   - Vehicle Search Plugin'i bulun
   - "Aktifleştir" butonuna tıklayın

5. **Ayarları Yapılandırın**
   - "Vehicle Search Settings" bölümünden eklenti ayarlarını yapılandırın

## Yapılandırma

### Admin Panel Ayarları

Eklenti kurulumundan sonra admin panelde aşağıdaki ayarları yapılandırabilirsiniz:

- **AJAX Desteği**: Dinamik veri yükleme özelliğini etkinleştirin/devre dışı bırakın
- **Varsayılan Arama Türü**: Özellik tabanlı (M) veya kategori tabanlı (K) arama
- **Sayfa Başına Maksimum Sonuç**: Arama sonuçlarında gösterilecek maksimum ürün sayısı
- **Filtre Seçenekleri**: Hangi filtrelerin aktif olacağını belirleyin
- **Önbellek Süresi**: Veri önbellekleme süresini ayarlayın
- **Gelişmiş Arama**: Ek arama seçeneklerini etkinleştirin

### Veritabanı Tabloları

Eklenti aşağıdaki tabloları oluşturur:

- `tplugin_vehicle_search_config`: Eklenti yapılandırma ayarları
- `tplugin_vehicle_search_cache`: Önbellek verileri
- `tplugin_vehicle_search_stats`: Arama istatistikleri

## Kullanım

### Frontend Kullanımı

Eklenti kurulumundan sonra arama formu otomatik olarak aktif hale gelir. Kullanıcılar:

1. **Arama Türünü Seçer**: Özellik tabanlı veya kategori tabanlı
2. **Üretici Seçer**: Dropdown menüden araç üreticisini seçer
3. **Model Seçer**: Seçilen üreticiye göre mevcut modeller listelenir
4. **Tip Seçer**: Seçilen modele göre araç tipleri listelenir
5. **Arama Yapar**: Form doldurulduktan sonra arama yapılır

### Template Entegrasyonu

Eklentiyi özel template'lerinizde kullanmak için:

```smarty
{* Vehicle search form *}
{include file='plugins/VehicleSearchPlugin/frontend/templates/vehicle_search.tpl'}
```

## Paketleme

Dağıtım paketi hazırlamak için aşağıdaki adımları izleyin:

1. `VehicleSearchPlugin` klasörünün yerel kopyasını güncellediğinizden emin olun.
2. ZIP arşivini oluştururken sadece bu klasörü ve içindeki gerekli eklenti dosyalarını (`info.xml`, `Bootstrap.php`, `src/`, `frontend/`, `adminmenu/`, `Migrations/` vb.) dahil edin.
3. Arşiv kökünde fazladan üst klasör bırakmayın; ZIP açıldığında doğrudan `VehicleSearchPlugin/` klasörü görünmelidir.
4. Oluşturduğunuz `VehicleSearchPlugin.zip` dosyasını JTL Shop yönetim paneli üzerinden yükleyin veya elle eklenti klasörüne çıkarın.

### AJAX API

Eklenti aşağıdaki AJAX endpoint'lerini sağlar:

- `getManufacturers`: Üretici listesini getirir
- `getVehicleModels`: Seçilen üreticiye göre modelleri getirir
- `getVehicleTypes`: Seçilen modele göre tipleri getirir
- `getCategories`: Kategori listesini getirir
- `logSearch`: Arama istatistiklerini kaydeder

## Geliştirici Notları

### Dosya Yapısı

```
VehicleSearchPlugin/
├── adminmenu/           # Admin panel dosyaları
├── frontend/            # Frontend dosyaları
│   ├── css/            # CSS dosyaları
│   ├── js/             # JavaScript dosyaları
│   ├── templates/      # Smarty şablonları
│   └── ajax.php        # AJAX endpoint
├── Migrations/          # Veritabanı migration'ları
├── src/                # PHP sınıfları
├── info.xml            # Eklenti tanım dosyası
├── Bootstrap.php       # Eklenti bootstrap dosyası
└── README.md           # Bu dosya
```

### Hook'lar

Eklenti aşağıdaki JTL Shop hook'larını kullanır:

- `HOOK_HEADER_HTML`: CSS ve JavaScript dosyalarını header'a ekler
- `HOOK_FOOTER_HTML`: Footer'a ek JavaScript ekler

### Namespace

Eklenti `Plugin\VehicleSearchPlugin` namespace'ini kullanır.

## Sorun Giderme

### Yaygın Sorunlar

1. **Eklenti Yüklenmiyor**
   - Dosya izinlerini kontrol edin
   - JTL Shop sürümünün uyumlu olduğundan emin olun

2. **AJAX Çalışmıyor**
   - CSRF token'ın doğru ayarlandığından emin olun
   - JavaScript konsol hatalarını kontrol edin

3. **Veriler Yüklenmiyor**
   - Veritabanı bağlantısını kontrol edin
   - Önbellek ayarlarını kontrol edin

### Log Dosyaları

Hata ayıklama için JTL Shop log dosyalarını kontrol edin:
- `/logs/error.log`
- `/logs/debug.log`

## Lisans

Bu eklenti MIT lisansı altında lisanslanmıştır.

## Destek

Teknik destek için:
- E-posta: support@bremer-sitzbezuege.de
- Web: https://bremer-sitzbezuege.de

## Sürüm Geçmişi

### v1.0.0
- İlk sürüm
- JTL Shop 5.x uyumluluğu
- Temel araç arama özellikleri
- Admin panel yapılandırması
- AJAX desteği
- Önbellekleme sistemi