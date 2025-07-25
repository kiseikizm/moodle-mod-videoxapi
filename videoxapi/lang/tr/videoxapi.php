<?php
/**
 * Turkish strings for videoxapi
 *
 * @package    mod_videoxapi
 * @copyright   2024 Atlas University - İsmail AYDIN <kiseiki@hotmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['modulename'] = 'Video xAPI';
$string['modulenameplural'] = 'Video xAPI etkinlikleri';
$string['pluginname'] = 'Video xAPI';
$string['pluginadministration'] = 'Video xAPI yönetimi';
$string['settings'] = 'Video xAPI Ayarları';
$string['modulename_help'] = 'Video xAPI etkinliği, öğrencilerin xAPI bildirimleri aracılığıyla kapsamlı öğrenme analizleri izlemesi ile video izlemelerini sağlar.';

// Form strings.
$string['videoxapiname'] = 'Etkinlik adı';
$string['videoxapiname_help'] = 'Bu, öğrencilere gösterilecek video etkinliğinin adıdır.';

// Video source configuration.
$string['videosource'] = 'Video kaynağı';
$string['videosourcetype'] = 'Video kaynağı türü';
$string['videosourcetype_help'] = 'Video dosyasını Moodle’a yükleyip yüklemeyeceğinizi ya da harici bir video URL’si kullanıp kullanmayacağınızı seçin.';
$string['videourl'] = 'Video URL’si';
$string['videourl_help'] = 'Video dosyasına doğrudan bağlantı URL’sini girin. Desteklenen formatlar: MP4, WebM, OGG.';
$string['videofile'] = 'Video dosyası';
$string['videofile_help'] = 'Moodle’a bir video dosyası yükleyin. Maksimum dosya boyutu 10MB.';

// Video display settings.
$string['videodisplay'] = 'Video görüntü ayarları';
$string['videowidth'] = 'Video genişliği (piksel)';
$string['videowidth_help'] = 'Video oynatıcısının genişliğini piksel cinsinden ayarlayın (100–1920).';
$string['videoheight'] = 'Video yüksekliği (piksel)';
$string['videoheight_help'] = 'Video oynatıcısının yüksekliğini piksel cinsinden ayarlayın (100–1080).';
$string['responsivesizing'] = 'Duyarlı boyutlandırmayı etkinleştir';
$string['responsivesizing_help'] = 'Etkinleştirildiğinde, video oynatıcı farklı ekran boyutlarına uyacak şekilde otomatik olarak ayarlanır.';

// xAPI tracking settings.
$string['xapitracking'] = 'xAPI izleme ayarları';
$string['xapitrackinglevel'] = 'İzleme seviyesi';
$string['xapitrackinglevel_help'] = 'xAPI bildirimi oluşturma detay seviyesini seçin.';
$string['trackingdisabled'] = 'Kapalı - xAPI bildirimi yok';
$string['trackingbasic'] = 'Temel - Yalnızca oynat/durdur/tamamlandı';
$string['trackingstandard'] = 'Standart - Arama ve ilerleme dahil';
$string['trackingdetailed'] = 'Ayrıntılı - Tüm etkileşimler ve yer imleri';

// Bookmark settings.
$string['enablebookmarks'] = 'Yer imlerini etkinleştir';
$string['enablebookmarks_help'] = 'Öğrencilerin belirli video zaman damgalarında yer imi oluşturmasına izin verin.';
$string['bookmarkpermissions'] = 'Yer imi görünürlüğü';
$string['bookmarkpermissions_help'] = 'Öğrencilerin oluşturduğu yer imlerini kimlerin görebileceğini kontrol edin.';
$string['bookmarksall'] = 'Tüm kullanıcılar tüm yer imlerini görebilir';
$string['bookmarksown'] = 'Kullanıcılar yalnızca kendi yer imlerini görebilir';
$string['bookmarksnone'] = 'Yer imleri oluşturana özeldir';

// Completion settings.
$string['completionwatched'] = 'Öğrenci videoyu izlemeli';
$string['completionwatched_help'] = 'Tamamlama için öğrencilerin videonun belirli bir yüzdesini izlemesini zorunlu kılar.';
$string['completionwatchedpercent'] = 'Minimum izleme yüzdesi';

// Validation error messages.
$string['numeric'] = 'Bu alan sayısal olmalıdır';
$string['invalidurl'] = 'Lütfen geçerli bir URL girin';
$string['invalidvideoformat'] = 'URL bir video dosyasına işaret etmeli (mp4, webm, ogg, avi, mov, wmv, flv, m4v)';
$string['videourlnotaccessible'] = 'Video URL erişilebilir değil';
$string['videourlnotfound'] = 'Video URL hata döndürdü (404 veya benzeri)';
$string['invalidvideowidth'] = 'Video genişliği 100 ile 1920 piksel arasında olmalıdır';
$string['invalidvideoheight'] = 'Video yüksekliği 100 ile 1080 piksel arasında olmalıdır';

// General strings.
$string['privacy:metadata'] = 'Video xAPI eklentisi, öğrenme analizleri için xAPI bildirimleri oluşturmak üzere video etkileşim verilerini depolar.';
$string['privacy:metadata:videoxapi_bookmarks'] = 'Kullanıcılar tarafından oluşturulan yer imleri hakkında bilgi';
$string['privacy:metadata:videoxapi_bookmarks:userid'] = 'Yer imini oluşturan kullanıcının kimliği';
$string['privacy:metadata:videoxapi_bookmarks:timestamp'] = 'Yer iminin oluşturulduğu video zaman damgası';
$string['privacy:metadata:videoxapi_bookmarks:title'] = 'Yer imine verilen başlık';
$string['privacy:metadata:videoxapi_bookmarks:description'] = 'Yer imi açıklaması';
$string['privacy:metadata:videoxapi_bookmarks:timecreated'] = 'Yer iminin oluşturulma zamanı';

// xAPI Configuration strings.
$string['xapiconfig'] = 'xAPI Yapılandırması';
$string['xapiconfig_desc'] = 'xAPI bildirim izleme için Öğrenme Kayıt Deposu (LRS) ayarlarını yapılandırın.';
$string['xapienabled'] = 'xAPI izlemeyi etkinleştir';
$string['xapienabled_desc'] = 'xAPI bildirimlerinin oluşturulmasını ve LRS’ye gönderilmesini etkinleştirin veya devre dışı bırakın.';
$string['lrsendpoint'] = 'LRS uç nokta URL’si';
$string['lrsendpoint_desc'] = 'Öğrenme Kayıt Deposu uç nokta URL’niz (örn. https://lrs.example.com/xapi).';
$string['lrsusername'] = 'LRS kullanıcı adı';
$string['lrsusername_desc'] = 'LRS kimlik doğrulaması için kullanıcı adı.';
$string['lrspassword'] = 'LRS parolası';
$string['lrspassword_desc'] = 'LRS kimlik doğrulaması için parola.';
$string['lrsauthmethod'] = 'Kimlik doğrulama yöntemi';
$string['lrsauthmethod_desc'] = 'LRS iletişimi için kimlik doğrulama yöntemini seçin.';
$string['authbasic'] = 'Temel Kimlik Doğrulama';
$string['authoauth'] = 'OAuth Kimlik Doğrulama';

// Queue Configuration strings.
$string['queueconfig'] = 'Kuyruk Yapılandırması';
$string['queueconfig_desc'] = 'xAPI bildirimlerinin arka planda işlenmesini yapılandırın.';
$string['queueenabled'] = 'Kuyruk işlemeyi etkinleştir';
$string['queueenabled_desc'] = 'xAPI bildirimlerinin arka planda işlenmesini etkinleştirin. Performans için önerilir.';
$string['queuefrequency'] = 'Kuyruk işleme sıklığı';
$string['queuefrequency_desc'] = 'Kuyrukta bekleyen xAPI bildirimlerinin ne sıklıkla işleneceği.';
$string['queuebatchsize'] = 'Kuyruk parti boyutu';
$string['queuebatchsize_desc'] = 'Her partide işlenecek bildirim sayısı.';
$string['connectiontimeout'] = 'Bağlantı zaman aşımı';
$string['connectiontimeout_desc'] = 'LRS bağlantıları için zaman aşımı (saniye cinsinden).';

// Time frequency options.
$string['every1minute'] = 'Her dakika';
$string['every5minutes'] = 'Her 5 dakika';
$string['every15minutes'] = 'Her 15 dakika';
$string['every30minutes'] = 'Her 30 dakika';
$string['every1hour'] = 'Her saat';

// Test connection.
$string['testconnection'] = 'LRS bağlantısını test et';
$string['testconnectionbutton'] = 'Bağlantıyı Test Et';
$string['connectionsuccessful'] = 'LRS’ye bağlantı başarılı';
$string['connectionfailed'] = 'LRS bağlantısı başarısız: {$a}';

// Additional UI strings.
$string['novideo'] = 'Bu etkinlik için video yapılandırılmamış.';
$string['bookmarks'] = 'Yer İmleri';
$string['reports'] = 'Raporlar';
$string['viewreports'] = 'Raporları Görüntüle';

// Task strings.
$string['processxapiqueue'] = 'xAPI bildirim kuyruğunu işle';

// Event strings.
$string['eventcoursemoduleviewed'] = 'Ders modülü görüntülendi';

// Error strings.
$string['errorloadingvideo'] = 'Video yüklenirken hata oluştu';
$string['errorsendingtolrs'] = 'LRS’ye bildirim gönderilirken hata oluştu';
$string['errorsavingbookmark'] = 'Yer imi kaydedilirken hata oluştu';

// Capability strings.
$string['videoxapi:addinstance'] = 'Yeni Video xAPI etkinliği ekle';
$string['videoxapi:view'] = 'Video xAPI etkinliğini görüntüle';
$string['videoxapi:createbookmarks'] = 'Yer imi oluştur';
$string['videoxapi:viewownbookmarks'] = 'Kendi yer imlerini görüntüle';
$string['videoxapi:viewallbookmarks'] = 'Tüm yer imlerini görüntüle';
$string['videoxapi:deleteownbookmarks'] = 'Kendi yer imlerini sil';
$string['videoxapi:deleteanybookmarks'] = 'Herhangi bir yer imini sil';
$string['videoxapi:viewreports'] = 'Raporları görüntüle';
$string['videoxapi:exportreports'] = 'Raporları dışa aktar';
$string['videoxapi:configurexapi'] = 'xAPI ayarlarını yapılandır';

// Report strings.
$string['activityoverview'] = 'Etkinlik Genel Bakış';
$string['metric'] = 'Metrik';
$string['value'] = 'Değer';
$string['enrolledusers'] = 'Kayıtlı Kullanıcılar';
$string['totalbookmarks'] = 'Toplam Yer İmleri';
$string['userswithbookmarks'] = 'Yer İmi Olan Kullanıcılar';
$string['totalstatements'] = 'Toplam xAPI Bildirimleri';
$string['sentstatements'] = 'Gönderilen Bildirimler';
$string['pendingstatements'] = 'Bekleyen Bildirimler';
$string['failedstatements'] = 'Başarısız Bildirimler';
$string['exportoptions'] = 'Dışa Aktarma Seçenekleri';
$string['exportcsv'] = 'CSV olarak dışa aktar';
$string['exportpdf'] = 'PDF olarak dışa aktar';
$string['engagementreport'] = 'Katılım Raporu';
$string['bookmarksreport'] = 'Yer İmleri Raporu';
$string['overview'] = 'Genel Bakış';
$string['engagement'] = 'Katılım';
$string['student'] = 'Öğrenci';
$string['bookmarkcount'] = 'Yer İmi Sayısı';
$string['firstactivity'] = 'İlk Etkinlik';
$string['lastactivity'] = 'Son Etkinlik';
$string['timestamp'] = 'Zaman Damgası';
$string['title'] = 'Başlık';
$string['description'] = 'Açıklama';
$string['created'] = 'Oluşturuldu';

// Bookmark strings.
$string['nobookmarks'] = 'Yer imi bulunamadı.';
$string['bookmarksaved'] = 'Yer imi başarıyla kaydedildi.';
$string['bookmarkdeleted'] = 'Yer imi başarıyla silindi.';
$string['confirmdeletebookmark'] = 'Bu yer imini silmek istediğinizden emin misiniz?';
$string['duplicatebookmark'] = 'Bu zaman damgasında zaten bir yer imi mevcut.';
$string['bookmarksdisabled'] = 'Bu etkinlik için yer imleri devre dışı.';

// Privacy strings.
$string['privacy:metadata:lrs'] = 'Öğrenme Kayıt Deposuna gönderilen video etkileşim verileri';
$string['privacy:metadata:lrs:userid'] = 'xAPI bildirimleri için kullanıcı tanımlayıcısı';
$string['privacy:metadata:lrs:timestamp'] = 'Video etkileşiminin zaman damgası';
$string['privacy:metadata:lrs:videointeraction'] = 'Video etkileşim detayları (oynat, durdur, ara, vb.)';

// Video Configuration strings.
$string['videoconfig'] = 'Video Yapılandırması';
$string['videoconfig_desc'] = 'Video dosyası yükleme ve görüntü ayarlarını yapılandırın.';
$string['maxvideosize'] = 'Maksimum video dosyası boyutu (MB)';
$string['maxvideosize_desc'] = 'Video dosyası yüklemeleri için megabayt cinsinden izin verilen maksimum boyut. Varsayılan 100 MB.';

// Test connection additional strings.
$string['lrsnotconfigured'] = 'LRS uç noktası yapılandırılmamış';
$string['connectionerror'] = 'Bağlantı hatası';
$string['connectionsuccess'] = 'Bağlantı başarılı! LRS doğru şekilde yanıt veriyor.';
$string['connectionexception'] = 'Bağlantı sırasında istisna oluştu';
$string['connectiondetails'] = 'Bağlantı Detayları';
$string['notset'] = 'Ayarlanmamış';
$string['backtosettings'] = 'Ayarlara Geri Dön';
$string['configurelrs'] = 'LRS Yapılandır';