# Moodle Video xAPI Plugin (mod_videoxapi)

[![Moodle](https://img.shields.io/badge/Moodle-5.0+-blue.svg)](https://moodle.org/)
[![PHP](https://img.shields.io/badge/PHP-8.1+-purple.svg)](https://php.net/)
[![License](https://img.shields.io/badge/License-GPL%20v3-green.svg)](LICENSE)
[![Version](https://img.shields.io/badge/Version-1.0.0%20Beta-orange.svg)](version.php)

Moodle 5 için geliştirilmiş kapsamlı bir aktivite modülü. Öğrenci video etkileşimlerini xAPI (Tin Can API) ifadeleri aracılığıyla takip eder ve detaylı öğrenme analitiği sağlar. Video etkileşimlerini yakalayarak harici Öğrenme Kayıt Depolarına (LRS) gönderir.

> **🚧 Beta Sürüm**: Bu plugin şu anda beta aşamasındadır. Üretim ortamında kullanmadan önce kapsamlı testler yapmanız önerilir.

## 🎯 Temel Özellikler

- **🎬 Video.js Entegrasyonu**: Kapsamlı olay takibi ile modern HTML5 video oynatıcı
- **📊 xAPI 1.0.3 Uyumluluğu**: Öğrenme analitiği için standart xAPI ifadeleri üretir
- **🔗 Öğrenme Kayıt Deposu (LRS) Desteği**: Yeniden deneme mekanizmaları ile harici LRS'lere ifade gönderimi
- **🔖 Öğrenci Yer İmleri**: Öğrencilerin belirli video zaman damgalarında yer imi oluşturmasına olanak tanır
- **📈 Eğitmen Analitiği**: Detaylı katılım raporları ve istatistikleri
- **⚡ Arka Plan İşleme**: Daha iyi performans için kuyruk tabanlı ifade işleme
- **🔒 Gizlilik Uyumluluğu**: GDPR uyumlu veri işleme ve dışa aktarma işlevselliği
- **🎯 Tamamlama Takibi**: Moodle tamamlama sistemi ile entegrasyon
- **🌐 Çoklu Dil Desteği**: Uluslararasılaştırma desteği ile çoklu dil
- **♿ Erişilebilirlik**: WCAG 2.1 AA standartlarına uygun tasarım

## 🛠️ Teknik Gereksinimler

- **Moodle**: 5.0 veya üzeri (minimum build: 2024041600)
- **PHP**: 8.1 veya üzeri
- **Veritabanı**: MySQL 5.7+ veya PostgreSQL 10+
- **Web Tarayıcı**: HTML5 video desteği olan modern tarayıcı
- **HTTPS**: xAPI LRS bağlantıları için gerekli
- **cURL**: HTTP istekleri için PHP cURL eklentisi

## 📦 Kurulum

### Manuel Kurulum
1. Plugin dosyalarını indirin
2. Moodle kurulumunuzda `mod/videoxapi` klasörüne çıkarın
3. Site Yönetimi → Bildirimler sayfasını ziyaret ederek kurulumu tamamlayın
4. Site Yönetimi → Eklentiler → Aktivite modülleri → Video xAPI bölümünden xAPI ayarlarını yapılandırın

### Git ile Kurulum
```bash
cd /path/to/moodle/mod
git clone https://github.com/kiseikizm/moodle-mod-videoxapi.git videoxapi
cd videoxapi
git checkout main
```

### Composer Bağımlılıkları (Geliştirme için)
```bash
cd mod/videoxapi
composer install --no-dev
```

## ⚙️ Yapılandırma

### LRS (Öğrenme Kayıt Deposu) Kurulumu

1. **Site Yönetimi → Eklentiler → Aktivite modülleri → Video xAPI** sayfasına gidin
2. **xAPI takibini etkinleştirin**
3. **LRS endpoint URL'sini girin** (HTTPS olmalı)
4. **LRS kimlik doğrulama bilgilerini sağlayın**
5. **"Bağlantıyı Test Et" butonunu kullanarak bağlantıyı test edin**

### Kuyruk İşleme Yapılandırması

Plugin, xAPI ifadelerini işlemek için arka plan görevleri kullanır:

1. **Plugin ayarlarında kuyruk işlemeyi etkinleştirin**
2. **İşleme sıklığını ayarlayın** (önerilen: her 5 dakika)
3. **Batch boyutunu yapılandırın** (önerilen: batch başına 50 ifade)
4. **Cron görevinin düzenli çalıştığından emin olun**

### Güvenlik Ayarları

- **HTTPS zorunluluğu**: Tüm LRS bağlantıları için
- **Kimlik bilgisi şifreleme**: Veritabanında şifrelenmiş saklama
- **CSRF koruması**: Form gönderimlerinde güvenlik
- **Rol tabanlı erişim**: Yetki kontrolü sistemi

## 🎓 Kullanım Kılavuzu

### Eğitmenler İçin

1. **Kursa "Video xAPI" aktivitesi ekleyin**
2. **Video kaynağını yapılandırın** (URL veya dosya yükleme)
3. **Video boyutlarını ve takip seviyesini ayarlayın**
4. **Gerektiğinde yer imlerini etkinleştirin/devre dışı bırakın**
5. **Öğrenci katılımını analiz etmek için raporları görüntüleyin**
6. **Tamamlama kriterlerini belirleyin**
7. **xAPI verilerini dışa aktarın**

### Öğrenciler İçin

1. **Otomatik ilerleme takibi ile videoları izleyin**
2. **Önemli anlarda yer imleri oluşturun**
3. **Yer imi zaman damgalarını kullanarak gezinin**
4. **Kaldığınız yerden izlemeye devam edin**
5. **İzleme geçmişinizi takip edin**
6. **Yer imlerinizi yönetin ve düzenleyin**

## 📊 xAPI İfadeleri

Plugin aşağıdaki xAPI ifadelerini üretir:

### Temel Video Etkileşimleri
- **`played`**: Video oynatma başladığında
- **`paused`**: Video duraklatıldığında  
- **`seeked`**: Kullanıcı farklı zaman damgasına atladığında
- **`completed`**: Video izleme tamamlandığında
- **`resumed`**: Video izleme devam ettirildiğinde

### Gelişmiş Etkileşimler
- **`bookmarked`**: Kullanıcı yer imi oluşturduğunda
- **`experienced`**: İlerleme takibi ve diğer etkileşimler için
- **`interacted`**: Oynatıcı kontrolleri ile etkileşim
- **`accessed`**: Aktiviteye erişim

### xAPI İfade Yapısı
```json
{
  "actor": {
    "mbox": "mailto:student@example.com",
    "name": "Öğrenci Adı"
  },
  "verb": {
    "id": "http://adlnet.gov/expapi/verbs/played",
    "display": {"en-US": "played"}
  },
  "object": {
    "id": "http://moodle.site/mod/videoxapi/view.php?id=123",
    "definition": {
      "name": {"tr": "Video Başlığı"},
      "type": "http://adlnet.gov/expapi/activities/media"
    }
  },
  "context": {
    "instructor": {"name": "Eğitmen Adı"},
    "platform": "Moodle 5.0"
  }
}
```

### Genel Bakış Raporu
- **Kayıtlı kullanıcı sayısı**: Toplam öğrenci sayısı
- **Yer imi istatistikleri**: Oluşturulan yer imi sayısı ve dağılımı
- **xAPI bildirim istatistikleri**: Gönderilen, bekleyen ve başarısız bildirimler
- **Dışa aktarma seçenekleri**: CSV ve PDF formatlarında rapor dışa aktarma

### Katılım Raporu
- **Öğrenci bazında katılım metrikleri**: Her öğrenci için detaylı analiz
- **Yer imi sayıları ve etkinlik tarihleri**: Zaman bazlı aktivite takibi
- **Katılım seviyesine göre sıralama**: Performans analizi için sıralama
- **İlk ve son etkinlik tarihleri**: Öğrenci aktivite aralığı

### Yer İmleri Raporu
- **Tüm yer imleri zaman damgaları ile**: Kronolojik sıralama
- **Öğrenci bilgileri**: Yer imi oluşturan kullanıcı detayları
- **Başlık ve açıklama bilgileri**: Yer imi içerik detayları
- **Oluşturulma tarihi**: Zaman bazlı analiz

## 🔒 Gizlilik ve Güvenlik

- **GDPR uyumlu veri dışa aktarma ve silme**: Avrupa veri koruma standartları
- **Şifrelenmiş kimlik bilgisi saklama**: Güvenli LRS bağlantı bilgileri
- **Girdi doğrulama ve CSRF koruması**: Form güvenliği
- **Rol tabanlı erişim kontrolü**: Moodle yetki sistemi entegrasyonu
- **HTTPS zorunluluğu**: Güvenli veri iletimi
- **Audit log sistemi**: Tüm işlemlerin kayıt altına alınması

## 🎯 Yetkiler (Capabilities)

- `mod/videoxapi:addinstance` - Yeni Video xAPI etkinliği ekleme
- `mod/videoxapi:view` - Video xAPI etkinliklerini görüntüleme
- `mod/videoxapi:createbookmarks` - Yer imi oluşturma
- `mod/videoxapi:viewownbookmarks` - Kendi yer imlerini görüntüleme
- `mod/videoxapi:viewallbookmarks` - Tüm yer imlerini görüntüleme
- `mod/videoxapi:deleteownbookmarks` - Kendi yer imlerini silme
- `mod/videoxapi:deleteanybookmarks` - Herhangi bir yer imini silme
- `mod/videoxapi:viewreports` - Raporları görüntüleme
- `mod/videoxapi:exportreports` - Rapor verilerini dışa aktarma
- `mod/videoxapi:configurexapi` - xAPI ayarlarını yapılandırma

## 🔧 Sorun Giderme

### Video Oynatılmıyor
- Video URL'sinin erişilebilirliğini kontrol edin
- Video format uyumluluğunu doğrulayın (MP4, WebM, OGG, AVI, MOV, WMV, FLV, M4V)
- Harici video URL'leri için HTTPS kullandığınızdan emin olun
- Dosya boyutu limitlerini kontrol edin
- Tarayıcı uyumluluğunu test edin

### xAPI Bildirimleri Gönderilmiyor
- LRS yapılandırması ve kimlik bilgilerini doğrulayın
- LRS'ye ağ bağlantısını kontrol edin
- Kuyruk istatistiklerinde başarısız bildirimleri inceleyin
- Moodle loglarında hata mesajlarını kontrol edin
- LRS endpoint URL'sinin doğru olduğundan emin olun
- Kimlik doğrulama yöntemini kontrol edin

### Performans Sorunları
- Daha iyi performans için kuyruk işlemeyi etkinleştirin
- Batch boyutu ve sıklık ayarlarını optimize edin
- Yoğun kullanım sırasında sunucu kaynaklarını izleyin
- Cron görevlerinin düzenli çalıştığından emin olun
- Veritabanı performansını optimize edin

### Yer İmi Sorunları
- Yer imi izinlerini kontrol edin
- Çift yer imi oluşturma durumlarını kontrol edin
- JavaScript hatalarını tarayıcı konsolunda inceleyin

## 🚀 Geliştirme ve Test

### Test Komutları
```bash
# PHPUnit testlerini çalıştır
vendor/bin/phpunit mod/videoxapi/tests/

# Behat testlerini çalıştır
php admin/tool/behat/cli/run.php --tags=@mod_videoxapi

# JavaScript linting
npm run eslint -- mod/videoxapi/amd/src/

# Kod kalitesi kontrolü
php admin/tool/phpcs/cli/run.php mod/videoxapi/
```

### Veritabanı İşlemleri
```bash
# Veritabanı şemasını güncelle
php admin/cli/upgrade.php

# Geliştirme değişikliklerinden sonra cache'leri temizle
php admin/cli/purge_caches.php

# Plugin'i yeniden yükle
php admin/cli/uninstall_plugins.php --plugins=mod_videoxapi
php admin/cli/upgrade.php
```

## 📞 Destek

Destek ve hata raporları için plugin'in GitHub repository'sini ziyaret edin veya geliştirme ekibi ile iletişime geçin.

**Geliştirici**: Atlas University - İsmail AYDIN  
**E-posta**: kiseiki@hotmail.com  
**GitHub**: [Proje Repository'si]

## 📄 Lisans

Bu plugin GNU General Public License v3.0 altında lisanslanmıştır. Detaylar için LICENSE dosyasına bakın.

## 🏆 Katkıda Bulunanlar

Moodle 5 için Video.js entegrasyonu ve xAPI 1.0.3 uyumluluğu ile geliştirilmiştir.

**Ana Geliştirici**: İsmail AYDIN (Atlas University)  
**Teknoloji Stack**: PHP 8.1+, Moodle 5, Video.js, xAPI 1.0.3  
**Desteklenen Diller**: Türkçe, İngilizce
