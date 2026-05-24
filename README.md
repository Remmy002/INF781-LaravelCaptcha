# INF781 - Implementación de Mecanismos de Seguridad y CAPTCHA en Laravel

Este proyecto corresponde a la guía práctica del laboratorio de Seguridad de Software. Se enfoca en la mitigación de ataques automatizados mediante la integración de controles defensivos avanzados en formularios sensibles (Registro, Autenticación y Contacto), aplicando el principio de defensa en profundidad.

---

## Requisitos del Sistema

Antes de iniciar la instalación, asegúrate de contar con el siguiente entorno local:
* PHP >= 8.2
* Composer >= 2.x
* PostgreSQL >= 15
* Extensión GD de PHP activa (requerida para la generación del CAPTCHA local de imágenes).

---

## Instalación y Configuración

Sigue estos pasos para clonar e instalar el entorno de desarrollo:

1. **Clonar el repositorio:**
   ```bash
   git clone [https://github.com/tu_usuario/INF781-LaravelCaptcha.git](https://github.com/tu_usuario/INF781-LaravelCaptcha.git)
   cd INF781-LaravelCaptcha

2. **Instalar dependencias de backend:**
    ```bash
    composer install

3. **Configurar el archivo de entorno:**
    Copia el archivo de ejemplo y configura tus credenciales de la base de datos de PostgreSQL:
    ```bash
    cp .env.example .env
    ```

    Abre el archivo .env y edita los parámetros de conexión:
    Fragmento de código:
    DB_CONNECTION=pgsql
    DB_HOST=127.0.0.1
    DB_PORT=5432
    DB_DATABASE=nombre_tu_base_datos
    DB_USERNAME=tu_usuario
    DB_PASSWORD=tu_contraseña

4. **Generar la clave de aplicación:**
    ```bash
    php artisan key:generate

5. **Ejecutar las migraciones:**
    Crea las tablas del sistema base y del formulario de contacto:
    ```bash
    php artisan migrate

## Configuración de Claves de reCAPTCHA de Google

Configuración de Claves de reCAPTCHA de Google
Para habilitar la protección del formulario de Registro con reCAPTCHA v2 Invisible (según los requerimientos teóricos de producción):

1. Ve a la consola de Google reCAPTCHA.

2. Registra un nuevo sitio seleccionando el tipo reCAPTCHA v2 -> Distintivo de reCAPTCHA invisible.

3. Añade el dominio localhost o 127.0.0.1.

4. Copia la Clave del sitio y la Clave secreta.

5. Pégalas al final de tu archivo .env:
    -RECAPTCHA_SITE_KEY=tu_clave_del_sitio_aqui
    -RECAPTCHA_SECRET_KEY=tu_clave_secreta_aqui

## Ejecución del Proyecto

Iniciar el servidor local de desarrollo:

```bash
php artisan serve
```
El sistema estará disponible en de forma inmediata ingresando a http://localhost:8000.

Ejecutar la suite de pruebas automatizadas:
Para validar que los controladores, el honeypot y las restricciones de login pasen los controles de calidad en el entorno de pruebas, ejecuta:

```bash
php artisan test --filter=CaptchaProtectionTest
```

## Capturas de Pantalla de Formularios Protegidos

### 1. Formulario de Registro (reCAPTCHA v2)
![Registro](./public/capturas/register.png)

### 2. Formulario de Login (CAPTCHA de Imagen Local con Validación de Caché Global)
![Login](./public/capturas/login.png)

### 3. Formulario de Contacto (Defensa en Profundidad: Honeypot Oculto + Rate Limiting por IP)
![Contacto](./public/capturas/contact.png)


## Análisis Crítico y Discusión
22. ¿Qué amenazas específicas mitiga cada uno de los tres formularios protegidos y cuál es la amenaza residual que el CAPTCHA no resuelve?
A ver, analizando lo que armamos en el laboratorio... creo que cada formulario ataca un problema distinto según su exposición. El formulario de registro (que originalmente se planteó con reCAPTCHA) mitiga principalmente la creación masiva de cuentas falsas o el llamado Spam Registration. Si no tuviera esa traba, un script automatizado podría crearnos miles de usuarios en la base de datos de PostgreSQL en un ratito, llenando el almacenamiento con basura y afectando el rendimiento del servidor.

Por otro lado, el formulario de Login, donde pusimos nuestro LoginRequest personalizado, ataca directamente los ataques de fuerza bruta tradicionales y el credential stuffing. Con el RateLimiter (ese que bloquea por 5 intentos) controlamos que un atacante pruebe contraseñas como locos, y el CAPTCHA local evita que usen herramientas automatizadas de cracking automáticas. Luego está el formulario de contacto público. Ahí aplicamos "defensa en profundidad". El campo honeypot (ese input oculto por CSS) mitiga los bots recolectores de formularios más simples, que rellenan todo lo que pillan de forma ciega. Al caer ahí, los descartamos en el ContactController. El rate limiting por IP complementa esto evitando ataques de denegación de servicio (DoS) a nivel de aplicación que intenten saturar el correo o la base de datos con mensajes falsos.

Ahora, sobre la amenaza residual... bueno, supongo que el CAPTCHA no es mágico. No resuelve cosas como los ataques de ingeniería social o el phishing, donde un usuario real mete sus datos engañado en una página clonada. Tampoco detiene a los humanos reales contratados para resolver captchas en masa (las llamadas granjas de CAPTCHAs), ni los ataques lógicos si dejamos una vulnerabilidad en el código o una mala configuración en las sesiones del backend, como nos pasó hace rato. Un humano malintencionado siempre va a poder saltarse estas barreras visuales.

23. Compara reCAPTCHA v2 (Google) frente a mews/captcha en cinco dimensiones: seguridad, accesibilidad, privacidad del usuario, dependencia externa y experiencia. ¿En qué escenario preferirías cada uno?
Esta comparativa tiene varias mañas según el lado del software desde el que se mire. Si analizamos la seguridad, creo que gana reCAPTCHA v2 porque usa el análisis de riesgo adaptativo de Google deteniendo bots avanzados, mientras que un captcha local basado en imágenes estáticas (como el nuestro) puede ser vulnerado por librerías modernas de reconocimiento óptico de caracteres (OCR) con inteligencia artificial si las distorsiones no son muy complejas. En la accesibilidad, reCAPTCHA v2 ofrece alternativas de audio dinámicas bien optimizadas para cumplir normativas WCAG, mientras que las soluciones locales suelen excluir a usuarios con discapacidades visuales a menos que programemos un sistema de audio desde cero, lo cual es complicado.

Por el lado de la privacidad del usuario, la cosa cambia por completo. mews/captcha (o nuestra solución nativa con caché) es mil veces más privada porque todo se procesa localmente en el servidor, sin compartir cookies ni rastrear al cliente. Google, en cambio, analiza el comportamiento del navegador y recopila datos. En dependencia externa, la solución local gana porque funciona de forma autónoma offline; si los servidores de Google se caen o bloquean la API Key, el registro externo deja de funcionar. Finalmente, en la experiencia de usuario (UX), reCAPTCHA a veces es molesto si te obliga a marcar semáforos o hidrantes tres veces seguidas, mientras que escribir 5 letras locales es directo, aunque igual cansa si la imagen es muy borrosa.

¿Cuándo preferiría cada uno? Supongo que usaría reCAPTCHA v2 para un sitio web empresarial grande expuesto a internet masivo, donde el tráfico de bots es enorme y la accesibilidad legal es obligatoria. En cambio, preferiría la solución local para sistemas internos de una institución, intranets universitarias, paneles administrativos pequeños o proyectos donde la privacidad de los datos locales sea crítica y no se quiera regalar información a terceros.

24. Describe al menos dos formas conocidas de eludir un CAPTCHA de imágenes y propone defensas adicionales (rate limiting, MFA, análisis de comportamiento) que las contrarresten.
Pensando en cómo los atacantes rompen estas barreras... la primera forma más obvia y tecnológica hoy en día es el uso de redes neuronales convolucionales (CNN) combinadas con herramientas de OCR (Reconocimiento Óptico de Caracteres). Un desarrollador con conocimientos de Python puede entrenar un modelo con unas cuantas miles de imágenes de nuestro captcha para que la IA aprenda a limpiar las líneas de distorsión del fondo, a aislar los caracteres y a adivinar el texto en milisegundos con un porcentaje de acierto altísimo.

La segunda forma, que es menos técnica pero muy efectiva, es el uso de servicios de resolución humana o "Click Farms" (como 2Captcha o Anti-Captcha). Aquí el script del bot automatiza el proceso de navegación, pero cuando llega al login, extrae la imagen del captcha mediante la API y se la manda a un servicio externo donde un humano real en otra parte del mundo resuelve el código por fracciones de centavo. El bot recibe el texto devuelto, lo pega en el input y entra como si nada.

Para contrarrestar esto, no podemos confiar solo en el dibujo. Una defensa obligatoria es el rate limiting estricto combinado con bloqueos temporales progresivos por dirección IP y por cuenta. Si una IP resuelve 20 captchas correctos en un minuto, es un bot de una granja humana y hay que banearlo. Otra defensa es meter análisis de comportamiento (como rastrear el movimiento del mouse antes de hacer clic o el tiempo de llenado del formulario). Y por último, para acciones críticas como transferencias o cambios de clave, se debe exigir autenticación de múltiples factores (MFA) mediante códigos TOTP al celular o correo, porque un resolvedor de captchas o un OCR no tendrán acceso físico al dispositivo del usuario legítimo.

25. ¿Qué problemas de privacidad introduce reCAPTCHA al usuario final? Investiga sobre el rastreo de Google y las críticas del GDPR en la Unión Europea.
Pensado en esto de reCAPTCHA de Google tiene un trasfondo bastante polémico que al principio uno ignora cuando programa. El problema principal es que reCAPTCHA no es una simple herramienta aislada; para decidir si eres un humano o un robot, el script carga recursos desde los servidores de Google y examina activamente los componentes del navegador del usuario. Esto incluye leer las cookies activas de Google (las que determinan si estás logueado en YouTube o Gmail), el historial de navegación reciente si está disponible, la resolución de la pantalla, el huso horario, los patrones de clic y los movimientos del cursor.

Toda esta recolección masiva se traduce en una técnica de rastreo (fingerprinting) para alimentar las bases de datos publicitarias de Google sin que el usuario haya dado su consentimiento explícito para ser vigilado, simplemente quería llenar un formulario de contacto. Debido a esto, la Unión Europea, mediante el Reglamento General de Protección de Datos (GDPR), ha lanzado críticas durísimas y ha sancionado a varias plataformas. Las autoridades europeas argumentan que el uso de reCAPTCHA transfiere datos personales de ciudadanos de la UE hacia servidores en Estados Unidos de forma ilegal, violando los principios de minimización de datos y transparencia.

El mayor problema legal es la falta de opción: el usuario está obligado a aceptar que Google lo vigile si es que quiere enviar el mensaje, eliminando la opción del consentimiento libre. Además, Google utiliza de forma indirecta el trabajo gratuito de millones de humanos para entrenar sus propios modelos de inteligencia artificial y reconocimiento de imágenes (como digitalizar libros o mapear calles en Street View), todo esto oculto bajo una supuesta herramienta de seguridad web.



## Licencia y Autor

    Licencia: *** License

    Autor: Estudiante de Ingeniería Informática - U.A.T.F.