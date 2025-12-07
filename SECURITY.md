# Security Policy

**[English](#english) | [Русский](#русский)**

---

## English

### Supported Versions

The following versions of GiApiRoute are currently supported with security updates and bug fixes:

| Version | Status | Support Until |
|---------| ------ | -------------- |
| 1.x     | :white_check_mark: Supported | Active |


**Note:** We recommend always using the latest version to ensure you have all security patches and improvements.

### Reporting a Vulnerability

If you discover a security vulnerability in GiApiRoute, please follow the responsible disclosure process:

#### How to Report

1. **Do NOT create a public GitHub issue** for security vulnerabilities
2. Send a detailed report to: **[gammaironak@gmail.com](mailto:gammaironak@gmail.com)**
3. Include the following information in your report:
    - Description of the vulnerability
    - Affected version(s)
    - Steps to reproduce (if applicable)
    - Potential impact
    - Suggested fix (if you have one)

#### What to Expect

- **Initial Response:** You will receive a confirmation of receipt within 48 hours
- **Investigation:** Our security team will investigate the issue within 5-7 business days
- **Timeline:** Security patches will be released as soon as possible, typically within 30 days
- **Notification:** You will be notified when the vulnerability is patched
- **Credit:** If desired, your name/organization will be credited in the security advisory

#### Our Commitment

- We treat all security reports seriously and with confidentiality
- We will not disclose your report publicly without your permission
- We are committed to fixing security vulnerabilities promptly
- We follow industry best practices for responsible disclosure

### Security Best Practices

When using GiApiRoute, please follow these security recommendations:

#### Authentication & Authorization

- Always use authentication middlewares for protected endpoints
- Implement proper capability checks using `'capability'` middleware
- Use nonce validation with `'nonce'` middleware for state-changing operations
- Verify user roles and permissions before processing requests

#### Input Validation

- Always validate user input using `ValidatorMiddleware` or direct validation
- Sanitize and escape all output
- Use prepared statements when working with databases
- Follow WordPress security guidelines for data handling

#### Rate Limiting

- Implement rate limiting using the `'throttle'` middleware on public endpoints
- Adjust limits based on your API's requirements and server capacity
- Monitor for suspicious patterns or abuse

#### CORS & Headers

- Configure appropriate CORS policies
- Use security headers to prevent common attacks
- Validate `X-WP-Nonce` headers for CSRF protection
- Implement proper Content Security Policy (CSP)

#### Dependency Management

- Keep WordPress and all plugins updated
- Regularly update the Respect\Validation library and other dependencies
- Use tools like Composer to manage and audit dependencies
- Follow security advisories from the WordPress ecosystem

#### Logging & Monitoring

- Log all access attempts to sensitive endpoints
- Monitor for unusual activity or failed authentication attempts
- Keep audit logs of administrative actions
- Review logs regularly for security issues

### Known Security Considerations

- GiApiRoute relies on WordPress's native security mechanisms
- Always ensure WordPress is properly configured and updated
- Implement additional security layers at the server/hosting level
- Consider using Web Application Firewalls (WAF) for production environments

---

## Русский

### Поддерживаемые версии

Следующие версии GiApiRoute в настоящее время поддерживаются с обновлениями безопасности и исправлениями ошибок:

| Версия | Статус | Поддержка до |
|--------| ------ | ------------ |
| 1.x    | :white_check_mark: Поддерживается | Активно |

**Примечание:** Рекомендуется всегда использовать последнюю версию, чтобы иметь все исправления безопасности и улучшения.

### Сообщение об уязвимостях

Если вы обнаружили уязвимость безопасности в GiApiRoute, пожалуйста, следуйте процессу ответственного раскрытия:

#### Как сообщить

1. **НЕ создавайте публичную GitHub issue** для уязвимостей безопасности
2. Отправьте подробный отчёт на: **[gammaironak@gmail.com](mailto:gammaironak@gmail.com)**
3. Включите следующую информацию в ваш отчёт:
    - Описание уязвимости
    - Затронутые версии
    - Шаги для воспроизведения (если применимо)
    - Потенциальное воздействие
    - Предложенное исправление (если у вас есть)

#### Что ожидать

- **Первоначальный ответ:** Подтверждение получения в течение 48 часов
- **Расследование:** Наша команда безопасности изучит проблему в течение 5-7 рабочих дней
- **Временные рамки:** Исправления безопасности будут выпущены как можно скорее, обычно в течение 30 дней
- **Уведомление:** Вы будете уведомлены при выпуске исправления
- **Благодарность:** При желании ваше имя/организация будут указаны в рекомендации по безопасности

#### Наша приверженность

- Мы серьезно относимся ко всем отчётам о безопасности и конфиденциально
- Мы не будем разглашать ваш отчёт публично без вашего разрешения
- Мы обязуемся оперативно исправлять уязвимости безопасности
- Мы следуем лучшим практикам отрасли для ответственного раскрытия

### Рекомендации по безопасности

При использовании GiApiRoute, пожалуйста, следуйте этим рекомендациям:

#### Аутентификация и авторизация

- Всегда используйте middleware аутентификации для защищённых эндпоинтов
- Реализуйте правильные проверки возможностей с помощью middleware `'capability'`
- Используйте проверку nonce с middleware `'nonce'` для операций, изменяющих состояние
- Проверяйте роли и разрешения пользователя перед обработкой запросов

#### Валидация входных данных

- Всегда валидируйте пользовательский ввод с помощью `ValidatorMiddleware` или прямой валидации
- Обезвреживайте и экранируйте все выходные данные
- Используйте подготовленные запросы при работе с базами данных
- Следуйте рекомендациям WordPress по обработке данных

#### Ограничение скорости

- Реализуйте ограничение скорости с помощью middleware `'throttle'` на открытых эндпоинтах
- Отрегулируйте пределы в зависимости от требований вашего API и ёмкости сервера
- Мониторьте подозрительные паттерны или злоупотребление

#### CORS и заголовки

- Настройте соответствующие политики CORS
- Используйте заголовки безопасности для предотвращения распространённых атак
- Валидируйте заголовки `X-WP-Nonce` для защиты от CSRF
- Реализуйте надлежащую политику безопасности контента (CSP)

#### Управление зависимостями

- Держите WordPress и все плагины в актуальном состоянии
- Регулярно обновляйте библиотеку Respect\Validation и другие зависимости
- Используйте Composer для управления и аудита зависимостей
- Следите за рекомендациями по безопасности из экосистемы WordPress

#### Логирование и мониторинг

- Логируйте все попытки доступа к конфиденциальным эндпоинтам
- Мониторьте необычную активность или неудачные попытки аутентификации
- Ведите журналы аудита административных действий
- Регулярно проверяйте логи на предмет проблем безопасности

### Известные соображения безопасности

- GiApiRoute полагается на встроенные механизмы безопасности WordPress
- Всегда убедитесь, что WordPress правильно настроен и обновлён
- Реализуйте дополнительные уровни безопасности на уровне сервера/хостинга
- Рассмотрите возможность использования брандмауэров веб-приложений (WAF) для production окружения
