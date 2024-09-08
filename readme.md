# Todo APP

Bu proje, görevlerin çeşitli sağlayıcılardan alınıp atanmasını yöneten bir backend uygulamasıdır. Proje, Docker kullanarak kolay bir şekilde çalıştırılabilir ve yönetilebilir.

## İçindekiler

- [Proje Açıklaması](#proje-açıklaması)
- [Kurulum](#kurulum)
    - [Docker ve Docker Compose Kurulumu](#docker-ve-docker-compose-kurulumu)
    - [Proje Kurulumu](#proje-kurulumu)
- [Kullanım](#kullanım)
    - [Görevlerin Çekilmesi](#görevlerin-çekilmesi)
    - [Görevlerin Atanması](#görevlerin-atanması)
- [Erişim](#erişim)
- [Kullanılan Teknolojiler](#kullanılan-teknolojiler)

## Proje Açıklaması

Bu proje, geliştiricilere görevlerin çeşitli sağlayıcılardan çekilmesini ve atanmasını sağlayan bir sistem sunar. Görevler API'lerden alınır ve geliştiricilere atanır, böylece iş yükü dengeli bir şekilde dağıtılır.

## Kurulum

### Docker ve Docker Compose Kurulumu

1. Docker ve Docker Compose'un sisteminizde kurulu olduğundan emin olun. Eğer kurulu değilse, [Docker'ı](https://docs.docker.com/get-docker/) ve [Docker Compose'u](https://docs.docker.com/compose/install/) kurmak için ilgili dokümanları takip edin.

2. Docker ve Docker Compose kurulumunu tamamladıktan sonra terminal üzerinden projeyi klonlayın:

   ```bash
   git clone https://github.com/kullanıcı_adı/proje_adı.git
   cd proje_adı```

## Proje Kurulumu

1. **Docker konteynerlerini oluşturmak ve başlatmak için şu komutu çalıştırın:**

   ```bash
   docker-compose up -d```
2. **Veritabanı için gerekli migration işlemlerini yapın:**

   ```bash
   docker-compose exec app php bin/console doctrine:migrations:migrate```
3. **Seeder'ları çalıştırarak örnek veriler ekleyin:**

    ```bash
   docker-compose exec app php bin/console doctrine:fixtures:load
   ```
4. **`.env` dosyasında sağlayıcıların path'lerini girin. Varsayılan olarak, `.env` dosyasındaki path'ler aşağıdaki gibidir:**
    ```env
    PROVIDER1_API_URL=http://example.com/api/provider1
    PROVIDER2_API_URL=http://example.com/api/provider2
   ```
## Kullanım

### Görevlerin Çekilmesi

Görevleri çekmek için aşağıdaki komutları kullanabilirsiniz:

```bash
docker-compose exec app php bin/console app:fetch-tasks --provider=provider1
docker-compose exec app php bin/console app:fetch-tasks --provider=provider2
```

Bu komutlar, belirtilen sağlayıcılardan görevleri çeker ve veritabanına kaydeder. provider1 veya provider2 olarak sağlayıcıları belirleyebilirsiniz.

### Görevlerin Atanması
Görevleri geliştiricilere atamak için şu komutu kullanın:

```bash
docker-compose exec app php bin/console app:assign-tasks
```

Bu komut, veritabanındaki görevleri geliştiricilere atar ve her geliştiricinin iş yükünü dengeler.

### Erişim

Projeniz çalışmaya başladıktan sonra aşağıdaki URL'lerden erişebilirsiniz:

- [Tüm Görevler](http://localhost:8080/tasks)
- [Görev Detayı](http://localhost:8080/task/{id}) (Burada `{id}` görev ID'si ile değiştirilmelidir.)
- [Haftalık Planlar](http://localhost:8080/weekly-plans)

Bu sayfalar, görevlerin ve haftalık planların detaylarını görüntülemenizi sağlar.

### Kullanılan Teknolojiler

- **Factory Pattern:** `ProviderFactory`, `Provider` nesnelerini oluşturur.
- **Strategy Pattern:** `Provider1` ve `Provider2`, veri çekme stratejilerini uygular.
- **Repository Pattern:** Veritabanı işlemleri `Task`, `Developer`, ve `WeeklyPlan` sınıfları ile yönetilir.
- **Facade Pattern:** `TaskAssigner`, görevlerin atanması sürecini basit bir arayüzle sunar.

### Ek Bilgiler

- Proje, Symfony framework'ü üzerinde geliştirilmiştir.
- Docker ve Docker Compose kullanılarak kolayca yönetilebilir.
- Veritabanı olarak MySQL kullanılmaktadır ve varsayılan bağlantı bilgileri `.env` dosyasında tanımlanmıştır.