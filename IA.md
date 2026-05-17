# Uso de IA en este proyecto

## 1. Herramientas utilizadas

| Herramienta | Versión / Modelo | Modo de uso | Aprox. % del trabajo |
|---|---|---|---|
| Claude | Claude Sonnet 4.6 | Planificación por fases y generación inicial de borradores de código | 55% |
| ChatGPT | GPT-5.5 Thinking | Revisión de errores, dudas técnicas y apoyo con documentación | 20% |
| Ninguna | — | Revisión manual, pruebas, correcciones, commits y decisiones finales | 25% |

## 2. Configuración del proyecto

### CLAUDE.md / AGENTS.md

No he usado un archivo `CLAUDE.md` ni `AGENTS.md` con instrucciones globales del proyecto.

He usado Claude desde la web, no Claude Code CLI. Por eso no hay configuración local de agente, `settings.json`, sub-agentes ni MCPs configurados en el repositorio.

La carpeta `.claude/commands/` se dejó preparada al crear la estructura inicial, pero no llegué a usar comandos personalizados.

### settings.json u otra configuración equivalente

No he usado ningún `settings.json` ni configuración equivalente.

## 3. Skills personalizadas

No he usado skills personalizadas.

## 4. Slash commands personalizados

No he usado slash commands personalizados.

La carpeta `.claude/commands/` está en el repo, pero está vacía porque no se crearon comandos propios.

## 5. Sub-agentes invocados

No he usado sub-agentes ni Plan Mode.

## 6. MCPs

| MCP | Para qué lo usé | Qué aportó |
|---|---|---|
| Ninguno | No se conectó ningún MCP | — |

Con más tiempo habría sido útil usar algún MCP o documentación oficial de PrestaShop para comprobar mejor los hooks disponibles en PrestaShop 1.7.8.11.

## 7. Prompts importantes

### Prompt 1

- **Herramienta:** Claude
- **Prompt:** Le pedí que planteara la estructura inicial de un módulo PrestaShop 1.7 llamado `productbadges`.
- **Qué generó:** Carpetas principales, estructura del repositorio y primeros archivos base.
- **Qué hice con el output:** Lo usé como punto de partida, pero revisé la estructura según el enunciado y añadí archivos que faltaban.

### Prompt 2

- **Herramienta:** Claude
- **Prompt:** Le pedí el esqueleto principal del módulo y los scripts SQL de instalación/desinstalación.
- **Qué generó:** `productbadges.php`, `install.php` y `uninstall.php`.
- **Qué hice con el output:** Revisé el flujo de instalación/desinstalación, cambié el autor, añadí índices y ajusté el orden de limpieza en `uninstall()`.

### Prompt 3

- **Herramienta:** Claude
- **Prompt:** Le pedí crear el `ObjectModel` `ProductBadge`.
- **Qué generó:** La clase `ProductBadge` con campos, soporte multilenguaje/multitienda y métodos para consultar/asignar productos.
- **Qué hice con el output:** Añadí una protección extra al límite de badges y revisé que los IDs se castearan correctamente.

### Prompt 4

- **Herramienta:** Claude
- **Prompt:** Le pedí el controlador admin para gestionar badges desde el back office.
- **Qué generó:** `AdminProductBadgesController.php` con listado, formulario, validaciones y asignación de productos.
- **Qué hice con el output:** Corregí el nombre del submit, revisé validaciones y añadí la validación estricta de posición.

### Prompt 5

- **Herramienta:** Claude
- **Prompt:** Le pedí CSS, JS, plantilla Smarty y `config.xml`.
- **Qué generó:** Archivos frontend y configuración del módulo.
- **Qué hice con el output:** Revisé que no usara librerías externas y que las variables estuvieran escapadas en la plantilla.

### Prompt 6

- **Herramienta:** Claude
- **Prompt:** Le pedí conectar los hooks frontend.
- **Qué generó:** Hooks para cargar assets y mostrar badges.
- **Qué hice con el output:** Revisé que se cargaran assets solo donde hacía falta y corregí la carga del modelo `ProductBadge`.

### Prompt 7

- **Herramienta:** ChatGPT
- **Prompt:** Fui pidiendo revisión de los archivos generados y dudas concretas antes de hacer commits.
- **Qué generó:** Revisión crítica de errores y recomendaciones.
- **Qué hice con el output:** Apliqué correcciones concretas y documenté los problemas detectados.

## 8. Errores de la IA que detecté

### Error 1 — Orden de limpieza en `uninstall()`

- **Qué generó la IA:** Llamaba a `parent::uninstall()` antes de borrar tablas, configuración y pestaña admin.
- **Por qué estaba mal:** Preferí limpiar primero los recursos propios del módulo y llamar al padre al final para controlar mejor la desinstalación.
- **Cómo lo corregí:** Moví `parent::uninstall()` al final del método.

### Error 2 — Límite de badges poco protegido

- **Qué generó la IA:** Usaba el valor de `$maxBadges` en el `LIMIT` de SQL sin limitarlo bien.
- **Por qué estaba mal:** Podía llegar un valor 0, negativo o demasiado alto.
- **Cómo lo corregí:** Añadí un clamp entre 1 y 10 antes de usarlo en la consulta.

### Error 3 — Validación insuficiente de `position`

- **Qué generó la IA:** Validaba `position` de forma demasiado genérica.
- **Por qué estaba mal:** Solo deberían aceptarse `top-left` y `top-right`.
- **Cómo lo corregí:** Añadí una lista blanca con `VALID_POSITIONS` y validación server-side en el controlador.

### Error 4 — `require_once` mal colocado

- **Qué pasó:** El `require_once` del modelo `ProductBadge` acabó colocado en el archivo incorrecto.
- **Por qué estaba mal:** El modelo no debe cargarse a sí mismo.
- **Cómo lo corregí:** Lo quité de `ProductBadge.php` y lo puse en `productbadges.php`, justo después de comprobar `_PS_VERSION_`.

### Error 5 — Nombre incorrecto del submit

- **Qué generó la IA:** Usó `submitAddbadge` / `submitEditbadge`.
- **Por qué estaba mal:** El controlador trabaja con la tabla `productbadges`, así que el submit real debía corresponder a ese nombre.
- **Cómo lo corregí:** Lo cambié por `submitAddproductbadges` y `submitAddproductbadgesAndStay`.

### Error 6 — Traducciones con hashes inventados

- **Qué generó la IA:** Archivos `es.php` y `en.php` con hashes de traducción legacy inventados.
- **Por qué estaba mal:** En PrestaShop esos hashes los genera el sistema. Si se inventan, puede que no traduzcan nada.
- **Cómo lo corregí:** Dejé archivos de traducción limpios y documenté que las claves reales pueden regenerarse desde el back office.

### Error 7 — Hook de listado dependiente del tema

- **Qué generó la IA:** Usó `displayProductListingHook` para listados.
- **Por qué podía fallar:** En PrestaShop 1.7.8.11 con tema Classic, el renderizado exacto en listados puede depender de la plantilla del tema.
- **Cómo lo gestioné:** Lo probé, comprobé que el hook quedaba registrado y dejé documentada la limitación en el README.

## 9. Partes que hice sin IA

Aunque usé IA como apoyo, hubo varias partes que hice manualmente:

- Crear el repositorio y organizar los archivos.
- Ejecutar comandos Git, crear commits y subir cambios.
- Revisar qué archivo iba en cada carpeta.
- Corregir rutas y errores de carga.
- Decidir no usar los hashes de traducción inventados.
- Instalar PrestaShop 1.7.8.11 con Docker.
- Copiar el módulo dentro del contenedor.
- Instalar el módulo desde el back office.
- Crear una badge de prueba.
- Revisar que el módulo apareciera en el gestor de módulos.
- Revisar que la pestaña de **Catálogo → Product Badges** apareciera.
- Comprobar los hooks desde **Diseño → Posiciones**.
- Hacer las capturas de prueba.
- Redactar y ajustar la documentación final.

## 10. Reflexión final

La IA me ayudó bastante a avanzar más rápido, sobre todo para dividir el trabajo por fases y generar primeros borradores de archivos.

Aun así, no acepté el código tal cual. Fui revisando las partes importantes, corrigiendo errores, probando el módulo en Docker y documentando lo que funcionaba y lo que quedaba como limitación.

Lo que más me aportó la IA fue velocidad. Lo que más cuidado requirió fue no confiar en todo directamente, porque aparecieron errores sutiles en hooks, traducciones, submits y validaciones.

Si repitiera el ejercicio, usaría antes una instalación real de PrestaShop para validar cada fase más rápido y comprobaría los hooks disponibles desde el principio.