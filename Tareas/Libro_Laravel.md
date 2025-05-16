LARAVEL
Aquí continúa tu camino en el desarrollo de
aplicaciones web en PHP con Laravel
12ANDRÉS CRUZ YORIS
Primeros pasos con Laravel 12
Aquí continúa tu camino en el desarrollo de aplicaciones web en PHP con
Laravel
Andrés Cruz Yoris
Esta versión se publicó: 2025-03-03
¡Postea sobre el libro!
Por favor ayuda a promocionar este libro.
El post sugerido para este libro es:
¡Acabo de comprar el libro ”Primeros pasos con Laravel 12!” de @LibreDesarrollo!
Hazte con tu copia en:
https://www.desarrollolibre.net/libros/primeros-pasos-laravel
Sobre el autor
Este libro fue elaborado por Andrés Cruz Yoris, Licenciado en Computación, con más de 10 años de experiencia
en el desarrollo de aplicaciones web en general; trabajo con PHP, Python y tecnologías del lado del cliente como
HTML, JavaScript, CSS, Vue entre otras; y del lado del servidor como Laravel, Flask, Django y CodeIgniter.
También soy desarrollador en Android Studio, xCode y Flutter para la creación de aplicaciones nativas para
Android e IOS.
Pongo a tú disposición parte de mi aprendizaje, reflejado en cada una de las palabras que componen este libro,
mi tercer libro en el desarrollo de software, pero el segundo libro sobre el desarrollo web; de mi framework
favorito en este ámbito que viene siendo Laravel; un framework que viene siendo todo un reto por dos motivos:
1. Por lo grande y cantidad de características que trae.
2. Por las constantes actualizaciones que nos obligan a estar siempre pendiente de nuevos y fascinantes
cambios.
Copyright
Ninguna parte de este libro puede ser reproducido o transmitido de ninguna forma; es decir, de manera
electrónica o por fotocopias sin permiso del autor.
Prólogo
Laravel es un framework fascinante, inmenso y con una curva de aprendizaje algo elevada si es el primer
framework de este tipo al cual te vas a enfrentar.
Laravel nos ofrece varios esquemas para hacer lo mismo; una aplicación web; mediante un MVC que ya no lo es
tanto debido a tan diversos caminos que puedes tomar; y por eso, el propósito de lo aquí escrito; para que tu
camino para aprender este framework sea menos empinado y más fácil de seguir.
Laravel es un estupendo framework que puedes emplear para crear verdaderas aplicaciones reales y escalables
en el tiempo; cuando inicies a trabajar con Laravel te darás cuenta de las enormes cantidades de componentes y
funciones que cuenta el framework; lo que no forme parte oficial del framework, seguramente existe un paquete
que te permita solventar dicho requerimiento; y, lo que no te permite realizar Laravel del lado del cliente, puedes
emplear Node (y su enorme ecosistema), con cualquier paquete que trabaja del lado del cliente, por mencionar
algunos:
1. Vue
2. React
3. Angular
Para trabajar también desde el lado del cliente; todo esto, en un mismo proyecto.
Veremos como comos crear controladores, componentes, vistas modelos, administrar la base de datos mediante
las migraciones, crear aplicaciones reales, rest apis, generar datos de pruebas, entre otros aspectos fascinantes
del framework e inclusive interconectar proyectos en Laravel con Vue en su versión 3.
En definitiva, tendrás un enorme material a tu disposición, para hacer verdaderas aplicaciones.
Para quién es este libro
Este libro está dirigido a cualquiera que quiera comenzar a desarrollar con Laravel, aunque no se recomienda a
aquellas personas que no hayan trabajado con otros frameworks PHP, si es tu caso, te aconsejo, que primero
conozcas y practiques con frameworks similares, pero más sencillos, como es el caso de CodeIgniter 4, del cual
dispongo de muchos recursos que pueden servirte para introducirte en este mundo de frameworks PHP, en mi
sitio web encontrarás más información.
Laravel es un framework avanzado, aunque en el libro hago todo lo posible para mantener el desarrollo sencillo,
recuerda puedes practicar con frameworks similares, como el de CodeIgniter, del cual también cuento con un
libro y un curso; que es ideal para conocer un framework para dar los primeros pasos con este tipo de
tecnologías, ya que Laravel, tiende a tener una curva de aprendizaje más elevada al tener más componentes y
más abstracción al emplear los mismos.
Para aquellos que quieran conocer el framework y que conozcan otros frameworks similares en PHP, pero no
tienen los conocimientos necesarios para aventurarse en estos por no conocer las bases que los sustentan.
Para aquellas personas que quieran aprender algo nuevo, conocer sobre un framework que, aunque tiene mucha
documentación, la mayoría está en inglés y al estar el framework en constante evolución, tiende a quedar
desactualizada.
Para las personas que quieran mejorar una habilidad en el desarrollo web, que quiera crecer como desarrollador
y que quiera seguir escalando su camino con otros frameworks superiores a este; con que te identifiques al
menos con alguno de los puntos señalados anteriormente, este libro es para ti.
Consideraciones
Recuerda que cuando veas el símbolo de $ es para indicar comandos en la terminal; este símbolo no lo tienes
que escribir en tu terminal, es una convención que ayuda a saber que estás ejecutando un comando.
Al final de cada capítulo, tienes el enlace al código fuente para que lo compares con tu código.
El uso de las negritas en los párrafos tiene dos funciones:
1. Si colocamos una letra en negritas es para resaltar algún código como nombre de variables, tablas o
similares.
2. Resaltar partes o ideas importantes.
Para mostrar tips usamos el siguiente diseño:
Tips importantes
Para los fragmentos de código:
$ npm -v
Al emplear los *** o //***
Significa que estamos indicando que en el código existen fragmentos que presentamos anteriormente.
La página oficial del framework viene siendo:
https://laravel.com/
La cual es fundamental para seguir este libro y con esto conocer más aspectos del framework.
Como recomendación, emplea Visual Studio Code como editor, ya que, es un editor excelente, con muchas
opciones de personalización, extensiones, intuitivo, ligero y que puedes desarrollar en un montón de plataformas,
tecnologías, frameworks y lenguajes de programación; así que, en general, Visual Studio Code será un gran
compañero para ti; pero si prefieres otros editores como Sublime Text, PHPStorm o similares, puedes usarlo sin
ningún problema.
https://code.visualstudio.com/
Como navegador web, te recomiendo Google Chrome; aunque es cierto que al momento de desarrollar en
tecnologías web, es recomendable emplear más de un navegador; al desarrollar específicamente para el lado del
servidor y no enfocarnos en desarrollar del lado del cliente; no tiene mucho sentido para este libro, que emplees
múltiples navegadores; dicho esto, puedes emplear cualquier otro navegador en caso de que Google Chrome no
sea de tu agrado.
https://www.google.com/intl/es/chrome/
Fe de errata y comentarios
Si tienes alguna duda, recomendación o encontraste algún error, puedes hacerla saber en el siguiente enlace:
https://www.desarrollolibre.net/contacto/create
Por mi correo electrónico:
desarrollolibre.net@gmail.com
O por Discord en el canal de Laravel:
https://discord.gg/sg85Zgwz96
Como recomendación, antes de reportar un posible problema, verifica la versión a la que te refieres y lo agregas
en tu comentario; la misma se encuentra en la segunda página del libro.
Introducción
Esta guía tiene la finalidad de dar los primeros pasos con Laravel; con esto, vamos a plantear dos cosas:
1. No es un libro que tenga por objetivo conocer al 100% Laravel, o de cero a experto, ya que, sería un
objetivo demasiado grande para el alcance de esta guía, si no conocer su ecosistema, que nos ofrece y
cómo funciona el mismo en base a varios ejemplos y/o aplicaciones pequeñas con alcances limitados.
2. Se da por hecho de que el lector tiene conocimientos al menos básicos sobre la estructura del framework;
por el alcance que tiene Laravel como framework, aunado a las tecnologías relacionadas que siempre
forman parte importante del mismo (como Node, Vue, Tailwind.css, Alpine.js, HTML, y relacionados) en
comparación con otros frameworks como CodeIgniter, resulta muy difícil hacer la convivencia con todas
estas tecnologías en un solo escrito; en varias partes del libro, mencionaré cuando sería recomendable
que consultes otras fuentes para que al menos conozcas los aspectos básicos de dichas tecnologías; en
mi canal de YouTube, al igual mi blog y plataforma de academia digital, cuento con múltiples recursos que
podrán ayudarte en estas introducciones; recuerda que el objetivo del libro es introducir a Laravel más no
sus tecnologías asociadas.
Laravel, al ser un framework más completo que otros similares; esto significa que tiene muchos más
componentes con los cuales trabajar; se da por hecho que el lector tiene cierto conocimiento básico sobre cómo
funciona este tipo de frameworks, como el uso o teoría de para qué funcionan las migraciones, el MVC, rutas,
entre otras; no es necesario que sepas cómo manejarlas, pero sí que entiendas la lógica detrás de todo esto; si
no los tienes, te recomiendo que veas mi primer libro de programación web en el cual damos los primeros pasos
con CodeIgniter, el cual es un framework estupendo con muchas coincidencias con Laravel, y al ser un
framework más pequeño y sencillo de manejar resulta más fácil de iniciar tu aprendizaje.
Finalmente; en comparación con otros libros, el enfoque será un poco más acelerado o general cuando aborde
las explicaciones de los elementos que conforman el framework; y esto es así por dos aspectos principales:
1. Quiero abordar la mayor cantidad de características de Laravel sin alargar de más el libro.
2. Esto no es un libro recomendado si es el primer framework PHP de este tipo al cual te enfrentas, por lo
tanto, siendo así, ya deberías de conocer estos aspectos de la estructura del framework.
Para seguir este libro necesitas tener una computadora con Windows, Linux o MacOS.
Mapa
Este libro tiene un total de 22 capítulos, se recomienda que leas en el orden en el cual están dispuestos y a
medida que vayamos explicando los componentes del framework, vayas directamente a la práctica, repliques,
pruebes y modifiques los códigos que mostramos en este libro.
Capítulo 1: Se explica cuál es el software necesario, y la instalación del mismo para desarrollar en Laravel en
Windows con Laragon o Laravel Herd o en MacOS Laravel Herd y MacOS y Linux con Laravel Sail y Docker.
Capítulo 2: Hablaremos sobre Laravel, crearemos un proyecto, configuraremos la base de datos, conoceremos
aspectos básicos del framework y finalmente conoceremos el elemento principal que son las rutas.
Capítulo 3: Daremos los primeros pasos con las rutas y las vistas, para empezar a ver pantallas mediante el
navegador; también abordaremos el uso de los controladores con las vistas; redirecciones, directivas y blade
como motor de plantilla.
Capítulo 4: Conoceremos el uso de las migraciones, como elemento central para poder crear los modelos, que
son la capa que se conecta a la base de datos, a una tabla en particular; y, para tener esta tabla, necesitamos las
migraciones.
Capítulo 5: Conoceremos el MVC, que es el corazón y las bases del framework y, realizaremos unos pocos
ejemplos que nos servirán para seguir avanzando.
Capítulo 6: Crearemos una sencilla app tipo CRUD, aprenderemos a trabajar con el MVC, controladores de tipo
recurso, listados, paginación, validaciones de formulario, acceso a la base de datos entre otros aspectos
relacionados.
Capítulo 7: Conoceremos cómo enviar mensajes por sesión tipo flash las cuales usaremos para confirmación de
las operaciones CRUD y el uso de la sesión.
Capítulo 8: Este capítulo está orientado a aprender el uso de las rutas; que en Laravel son muy extensibles y
llenas de opciones para agrupamientos, tipos y opciones.
Capítulo 9: En este capítulo, vamos a crear un sistema de autenticación y todo lo que esto conlleva para nuestra
aplicación instalando Laravel Breeze, el cual también configura Tailwind.css en el proyecto y Alpine.js. También
vamos a expandir el esquema que nos provee Laravel Breeze para la autenticación, creando una protección en
base a roles, para manejar distintos tipos de usuarios en módulos específicos de la aplicación.
Capítulo 10: En este capítulo, vamos a conocer algunas operaciones comunes con Eloquent aplicados a la base
de datos mediante los query builders.
Capítulo 11: Vamos a presentar el uso de los componentes en Laravel como un elemento central para crear una
aplicación modular.
Capítulo 12: Aprenderemos a generar datos de prueba mediante clases usando el sistema de seeders que
incorpora el framework.
Capítulo 13: Aprenderemos a crear una Rest Api de tipo CRUD y métodos adicionales para realizar consultas
adicionales, también vamos a proteger la Rest Api de tipo CRUD con Sanctum, empleando la autenticación de
tipo SPA y por tokens.
Capítulo 14: Vamos a consumir la Rest Api mediante una aplicación tipo CRUD en Vue 3 empleando peticiones
axios y componentes web con Oruga UI; también veremos el proceso de carga de archivos. También
protegeremos la aplicación en Vue con login requerido para acceder a sus distintos módulos empleando la
autenticación SPA o por tokens de Laravel Sanctum.
Capítulo 15: Vamos a aprender a manejar la caché, para guardar datos de acceso para mejorar el desempeño de
la aplicación y evitar cuellos de botellas con la base de datos.
Capítulo 16: Vamos a aprender a manejar las políticas de acceso para agregar reglas de acceso a ciertos
módulos de la aplicación mediante los Gate y Policies.
Capítulo 17: Veremos cómo manejar los permisos y roles a un usuario para autorizar ciertas partes de la
aplicación con un esquema flexible y muy utilizado en las aplicaciones web de todo tipo usando Spatie, en este
capítulo conoceremos cómo realizar esta integración y desarrollaremos un módulo para manejar esta
permisología.
Capítulo 18: Veremos cómo manejar las relaciones uno a uno, uno a mucho, muchos a muchos a muchos y
polimórficas para reutilizar modelos que tengan un mismo comportamiento.
Capítulo 19: En este capítulo, veremos cómo manejar las configuraciones, variables de entorno, crear archivos
de ayuda, enviar correos, logging, colecciones, Lazy y Eager Loading, mutadores y accesores, colas y trabajos y
temas de este tipo que como comentamos anteriormente, son fundamentales en el desarrollo de aplicaciones
web.
Capítulo 20: En este capítulo, conoceremos paquetes importantes en Laravel para generar excels, qrs, seo,
PayPal, detectar navegación móvil entre otros.
Capítulo 21: Conoceremos cómo crear pruebas unitarias y de integración en la Rest Api y la app tipo blog
empleando PHPUnit y Pest.
Capítulo 22: Hablaremos sobre cómo puedes subir tu aplicación Laravel a producción.
Tabla de Contenido
Primeros pasos con Laravel 12 2
¡Postea sobre el libro! 3
Sobre el autor 4
Copyright 5
Capítulo 1: Software necesario e instalación de las herramientas 1
Windows y MacOS 1
Laravel Herd 2
Laragon 5
MacOS y Linux 9
Composer 9
Agregar composer en el PATH en MacOS 11
Configurar Git 11
Capítulo 2: Conociendo aspectos generales de Laravel 12
Organización de un proyecto 12
La carpeta app 12
La carpeta bootstrap 12
La carpeta de config 12
La carpeta database 12
La carpeta lang 12
La carpeta public 13
La carpeta de resources 13
La carpeta de routes 13
La carpeta storage 14
La carpeta de tests 14
La carpeta de vendor 14
La carpeta de app 14
La carpeta Http 14
La carpeta de Models 14
Crear un proyecto en Laravel 14
Windows 15
Con el instalador de Laravel 15
Con Composer 16
MacOS y Linux 17
MacOS con Laravel Herd 18
Con el instalador de Laravel 18
Con Composer 20
Ejecutar la aplicación de Laravel 20
Laragon 20
Laravel Sail 21
Laravel Herd 22
Primeros pasos con Laravel 23
Modo desarrollador 23
¿Cómo sabe Laravel qué configuración emplear, la del .env o de las de la carpeta config? 25
Configurar la base de datos MySQL 25
Windows con Laragon 26
Mac o Linux con Sail y Docker 28
Modelo vista controlador 30
Conociendo las rutas 32
Rutas con nombre 36
Artisan la línea de comandos 36
Capítulo 3: Rutas, controladores y vistas 38
Rutas y vistas 38
Caso práctico 38
Pase de parámetros 40
Redirecciones 41
Directivas en Laravel para blade (vistas) 42
Directiva if 42
Directiva foreach 42
Ordenar vistas en carpetas 42
Caso práctico 43
Layout o vista maestra 43
Vistas y controladores 44
Caso práctico 46
Rutas de tipo CRUD (recurso) 47
Argumentos en las rutas 49
Trabajando con las rutas 49
Crear elementos: 50
Leer elementos: 50
Actualizar elementos: 51
Borrar un elemento: 52
Rutas de tipo recurso 52
Función de compact 53
Capítulo 4: Migraciones 54
Migraciones 54
Crear una migración 57
Ejecutar la migración 58
Caso práctico 58
Tips para tus migraciones 61
Flujo de las migraciones 62
Revertir las migraciones (rollback) 62
Refrescar la base de datos 66
Capítulo 5: MVC y CRUD 67
Caso práctico 70
Crear un registro 70
Actualizar un registro 72
Eliminar un registro 74
Tipos devueltos en los métodos de los controladores 74
Relaciones foráneas 76
Capítulo 6: CRUD y formularios 81
Crear 81
Validar datos 85
Caso práctico 86
Validaciones en el controlador mediante el request 86
Validaciones en el controlador mediante el request, segunda forma 88
Validaciones mediante una clase FormRequest 89
Validar el slug 91
Mostrar errores del formulario 91
Listado 92
Listado paginado 93
Crear opciones CRUD 98
Crear un layout 99
Editar 100
Validar el slug 104
Fragmento de vista para los campos 105
Valores anteriores 106
Carga de imágenes/archivos 109
Eliminar 112
Vista de detalle 112
CRUD de categorías 113
Tinker, la consola interactiva de Laravel 120
Rutas agrupadas 120
Capítulo 7: Mensajes por sesión y flash 122
Mensajes tipo flash 122
Caso práctico 122
Sesión 124
Capítulo 8: Rutas 125
Nombre en las rutas 125
Parámetros 125
Parámetros obligatorios 125
Parámetros opcionales 126
Rutas agrupadas 127
Middlewares 127
Controladores 129
Agrupadas 130
Rutas de tipo recurso 131
Capítulo 9: Laravel Breeze 133
Sistema de autenticación 136
Configurar nuestra aplicación con Laravel Breeze 139
Configurar ruta 139
Configurar en layouts y vistas 139
Adaptar estilo al resto de la aplicación 144
Configurar tabla 145
Configurar formulario 146
Configurar container 147
Configurar los botones 147
Configurar la carta 148
Mensajes Flash 148
Otros estilos 149
Enlaces de navegación para los posts y categorías 151
Laravel Breeze variantes en la instalación 152
Vue 152
React 155
Manejo de roles 157
Caso práctico 157
Definir roles 157
Crear el middleware para la verificación de un usuario administrador 159
Capítulo 10: Operaciones comunes en Eloquent (ORM) 161
Ver el SQL 161
Joins 162
Ordenación 162
Where o orWhere anidados 162
WhereIn y WhereNotInt 163
Obtener un registro 163
Limitar la cantidad de registros 163
Cantidad 163
Obtener registros aleatorios 163
Lazy Loading y Eager Loading 164
Ventaja de la carga ansiosa 164
Desventaja de la carga ansiosa 164
Ventaja de la carga diferida 165
Desventaja de la carga diferida 165
Serialización 165
Restricciones de consulta 165
Limit y Offset 165
Optimización de consultas 166
Capítulo 11: Componentes 168
Estructura inicial 168
Componentes anónimos: Vista de listado 169
Slot 173
Slot por defecto 175
Slots con nombre 177
Slo
t
s
c
o
n
n
o
m
b
r
e
e
n
u
n
a lí
n
e
a
1
8
0
C
o
m
p
o
n
e
n
t
e
s
c
o
n
cla
s
e
s: Vis
t
a
d
e
d
e
t
alle
1
8
0 Invocar métodos 184
P
a
s
a
r
p
a
r
á
m
e
t
r
o
s
a lo
s
c
o
m
p
o
n
e
n
t
e
s
1
8
5
M
e
z
cla
r
a
t
rib
u
t
o
s
1
8
6
P
r
o
p
s
1
8
8
O
b
t
e
n
e
r
y
filt
r
a
r
a
t
rib
u
t
o
s
1
9
0
F
u
n
ció
n
d
e
fle
c
h
a
e
n
P
H
P
1
9
0
C
o
m
p
o
n
e
n
t
e
s
din
á
mic
o
s
1
9
1
O
c
ult
a
r
a
t
rib
u
t
o
s
/
m
é
t
o
d
o
s
1
9
1
A
s
o
cia
r
u
n
c
o
m
p
o
n
e
n
t
e
a
u
n
a
r
u
t
a
1
9
2
C
a
p
í
t
u
l
o
1
2
:
S
e
e
d
e
r
s
y
F
a
c
t
o
r
i
e
s
1
9
4
G
e
n
e
r
a
r
u
n
s
e
e
d
e
r: 1
9
4
C
a
s
o
p
r
á
c
tic
o
1
9
4
M
o
d
el f
a
c
t
o
rie
s
1
9
8
C
a
s
o
p
r
á
c
tic
o
1
9
9
C
a
p
í
t
u
l
o
1
3
:
R
e
s
t
A
p
i
2
0
3
C
a
s
o
p
r
á
c
tic
o
2
0
4
C
o
n
t
r
ola
d
o
r
e
s
2
0
4
M
a
n
ej
a
r
e
x
c
e
p
cio
n
e
s
2
0
7
P
r
o
b
a
r la
A
pi R
e
s
t
a
n
t
e
rio
r
2
0
8
P
r
o
b
a
r
C
R
U
D
d
e lo
s
p
o
s
t
s
2
1
0 Implementar métodos personalizados 216 Obtenerlas todas 216 Consumir por el slug 217
A
u
t
e
n
t
i
c
a
c
i
ó
n
p
a
r
a
l
a
R
e
s
t
A
p
i
2
2
0
A
u
t
e
n
tic
a
ció
n
p
a
r
a
u
n
a
w
e
b
S
PA
2
2
0
C
r
e
a
r
c
o
n
t
r
ola
d
o
r
p
a
r
a
el lo
gin
2
2
1
C
o
n
sid
e
r
a
ció
n im
p
o
r
t
a
n
t
e
s
o
b
r
e
el r
e
q
u
e
s
t
2
2
3
C
r
e
a
r
u
s
u
a
rio
d
e
p
r
u
e
b
a
2
2
3
P
r
u
e
b
a
s
c
o
n
P
o
s
t
m
a
n, V
u
e
y
p
r
o
t
e
c
ció
n
m
e
dia
n
t
e
S
a
n
c
t
u
m
2
2
4
P
r
o
t
e
g
e
r
r
u
t
a
s
m
e
dia
n
t
e
a
u
t
e
n
tic
a
ció
n
r
e
q
u
e
rid
a
2
2
7
A
u
t
e
n
tic
a
ció
n
e
n
b
a
s
e
a
t
o
k
e
n
s
2
2
9
C
r
e
a
r
t
o
k
e
n
s
2
2
9
C
a
p
í
t
u
l
o
1
4
:
C
o
n
s
u
m
i
r
R
e
s
t
A
p
i
d
e
s
d
e
V
u
e
3
2
3
4
A
g
r
e
g
a
r
V
u
e
3
al p
r
o
y
e
c
t
o
e
n
L
a
r
a
v
el 2
3
4
C
r
e
a
r
el P
r
o
y
e
c
t
o
e
n
V
u
e
2
3
5
C
o
n
fig
u
r
a
r
p
r
o
y
e
c
t
o
e
n
V
u
e
3
c
o
n
O
r
u
g
a
UI 2
3
7
G
e
n
e
r
a
r
u
n lis
t
a
d
o
2
4
0 Instalar Material Design Icons 243 Paginación 244 Ruteo con Vue Router 248 Instalación 248 Definir rutas 248
C
o
m
p
o
n
e
n
t
e
p
a
r
a
el r
e
n
d
e
riz
a
d
o
d
e lo
s
c
o
m
p
o
n
e
n
t
e
s
2
4
9
E
s
t
a
ble
c
e
r la
s
r
u
t
a
s
2
4
9
C
r
e
a
r
e
nla
c
e
s
2
4
9
C
o
m
p
o
n
e
n
t
e
p
a
r
a
c
r
e
a
r
y
e
dit
a
r
p
o
s
t
2
5
0
O
b
t
e
n
e
r la
s
c
a
t
e
g
o
r
í
a
s
2
5
2
C
r
e
a
r
u
n
p
o
s
t
c
o
n
v
alid
a
cio
n
e
s
2
5
3
E
dit
a
r
u
n
r
e
gis
t
r
o
2
5
8
Elimin
a
r
u
n
r
e
gis
t
r
o
2
6
0
P
a
r
á
m
e
t
r
o
s
o
p
cio
n
ale
s
p
a
r
a la
r
u
t
a
d
e
V
u
e
e
n
L
a
r
a
v
el 2
6
1
Tailwin
d.c
s
s
e
n
el p
r
o
y
e
c
t
o
e
n
V
u
e
c
o
n
O
r
u
g
a
UI 2
6
2
C
o
n
t
ain
e
r
2
6
2
C
a
m
bio
s
v
a
rio
s
e
n
el c
o
m
p
o
n
e
n
t
e
d
e lis
t
a
d
o
2
6
2
C
a
m
bio
s
v
a
rio
s
e
n
el c
o
m
p
o
n
e
n
t
e
d
e
g
u
a
r
d
a
d
o
2
6
4
M
e
n
s
aj
e
d
e
c
o
n
fir
m
a
ció
n
p
a
r
a
elimin
a
r
2
6
5
M
e
n
s
aj
e
d
e
a
c
ció
n
r
e
aliz
a
d
a
2
6
7
U
plo
a
d
d
e
a
r
c
hiv
o
s
2
6
9
R
e
c
u
r
s
o
R
e
s
t
2
6
9
V
u
e
3
y
c
o
m
p
o
n
e
n
t
e
u
plo
a
d
e
n
O
r
u
g
a
UI 2
7
0
M
a
n
ej
o
d
e
e
r
r
o
r
e
s
d
e
fo
r
m
ula
rio
2
7
3
O
p
cio
n
al: U
plo
a
d
d
e
a
r
c
hiv
o
s
v
í
a
D
r
a
g
a
n
d
D
r
o
p
2
7
4
B
o
r
r
a
r
a
r
c
hiv
o
s
a
n
t
e
rio
r
e
s
2
7
6
Mig
r
a
r
r
u
t
a
s
a
A
p
p.v
u
e
2
7
7
C
o
n
s
u
m
i
r
l
a
R
e
s
t
A
p
i
p
r
o
t
e
g
i
d
a
p
o
r
S
a
n
c
t
u
m
v
í
a
S
PA
y
t
o
k
e
n
s
2
7
8
L
o
gin: C
r
e
a
r
v
e
n
t
a
n
a
2
7
8
L
o
gin: O
b
t
e
n
e
r
t
o
k
e
n
2
8
1
M
a
n
ej
a
r
el t
o
k
e
n
d
e
a
u
t
e
n
tic
a
ció
n
e
n
V
u
e
2
8
2
R
e
dir
e
c
cio
n
e
s
e
n
el c
o
m
p
o
n
e
n
t
e
d
e lo
gin
2
8
5
E
n
via
r
t
o
k
e
n
e
n la
s
p
e
ticio
n
e
s
2
8
6
C
e
r
r
a
r la
s
e
sió
n
2
8
7
M
a
n
ej
a
r
el t
o
k
e
n
d
e
a
u
t
e
n
tic
a
ció
n
m
e
dia
n
t
e
u
n
a
C
o
o
kie
2
9
0 Instalar vue3-cookies 291 Configurar vue3-cookies con los datos de autenticación 292 Logout: Destruir la cookie del usuario 293 Verificar el token del usuario 293 Logout: Destruir el token del usuario 296
U
nific
a
r
To
k
e
n
y
s
e
sió
n
d
e
S
a
n
c
t
u
m
2
9
7
P
r
o
t
e
g
e
r
r
u
t
a
s
p
o
r
a
u
t
e
n
tic
a
ció
n
r
e
q
u
e
rid
a
2
9
8
D
e
t
a
l
l
e
s
f
u
n
c
i
o
n
a
l
e
s
f
i
n
a
l
e
s
2
9
9
D
e
t
alle
s
vis
u
ale
s
3
0
0
Ve
n
t
a
n
a
d
e lo
gin
3
0
0
C
o
n
t
ain
e
r
3
0
2
N
a
v
b
a
r
3
0
2
N
a
v
b
a
r: E
nla
c
e
s
d
e
n
a
v
e
g
a
ció
n
3
0
4
N
a
v
b
a
r: L
o
g
o
3
0
5
N
a
v
b
a
r: Av
a
t
a
r
3
0
6
N
a
v
b
a
r: D
e
t
alle
s
fin
ale
s
3
0
7
C
a
r
t
a
p
a
r
a lo
s
c
o
m
p
o
n
e
n
t
e
s
C
R
U
D
3
1
0
Blo
q
u
e
a
r
b
o
t
ó
n
d
e lo
gin
al m
o
m
e
n
t
o
d
el s
u
b
mit
3
1
1 Integrar la importación @ en vue en cualquier app en Laravel 312
C
a
p
í
t
u
l
o
1
5
:
C
a
c
h
é
3
1
4
U
s
o
b
á
sic
o
d
e la
c
a
c
h
é
3
1
4
C
a
c
h
e::g
e
t
(
)
3
1
5
C
a
c
h
e::h
a
s
(
)
3
1
5
C
a
c
h
e::p
u
t
(
)
3
1
5
C
a
c
h
e::p
u
t
M
a
n
y
(
)
3
1
5
C
a
c
h
e::a
d
d
(
)
3
1
6
C
a
c
h
e::p
ull(
)
3
1
6
C
a
c
h
e::m
a
n
y
(
)
3
1
6
C
a
c
h
e::r
e
m
e
m
b
e
r
(
)
3
1
6
C
a
c
h
e::r
e
m
e
m
b
e
r
F
o
r
e
v
e
r
(
)
3
1
6 Incremento o disminución de los valores en caché 317 Cache::forever() 317 Cache::forget() 317 Cache::flush() 317
C
a
s
o
p
r
á
c
tic
o
3
1
7
C
o
n
t
e
nid
o
J
S
O
N
e
n
R
e
s
t
A
pi 3
1
7
C
o
n
t
e
nid
o
H
T
M
L
o
f
r
a
g
m
e
n
t
o
d
e
vis
t
a
3
2
0
Tip
o
s
d
e
c
o
n
t
r
ola
d
o
r
e
s
3
2
2
A
r
c
hiv
o
3
2
2
B
a
s
e
d
e
d
a
t
o
s
3
2
2
M
e
m
c
a
c
h
e
d
3
2
2
R
e
dis
3
2
3
A
r
r
a
y
3
2
3
C
a
c
h
e
c
o
n
R
e
dis
3
2
3 Instalación de Redis 324 Configuraciones adicionales 327
C
a
c
h
é
d
e
r
u
t
a
s
3
2
8
C
a
s
o
p
r
á
c
tic
o
3
2
8
C
a
p
í
t
u
l
o
1
6
:
G
a
t
e
y
P
o
l
í
t
i
c
a
s
(
A
u
t
o
r
i
z
a
c
i
ó
n
)
3
3
1
A
u
t
e
n
tic
a
ció
n
y
a
u
t
o
riz
a
ció
n
3
3
1
C
a
m
bio
s iniciale
s
3
3
2
G
a
t
e
d
e
fin
e
y
allo
w, m
é
t
o
d
o
s
cla
v
e
s
3
3
4
P
olí
tic
a
s
p
a
r
a
a
g
r
u
p
a
r
r
e
gla
s
e
n
b
a
s
e
a
m
o
d
elo
s
3
3
5
C
r
e
a
n
d
o
u
n
a
p
olí
tic
a
3
3
5
R
e
gis
t
r
a
r la
p
olí
tic
a
3
3
7
U
s
a
r la
p
olí
tic
a
3
3
7
R
e
s
p
u
e
s
t
a
s
d
e la
s
p
olí
tic
a
s
3
3
9
M
o
dific
a
r
g
u
a
r
d
a
d
o
d
e
p
o
s
t
3
4
0
Métodos importantes 340
Gate::check() 340
Gate::any() y Gate::none() 341
User::can() y User::cannot() 341
Gate::forUser() 341
Gate::allowIf() 342
Gate::denyIf() 342
Gate::authorize() 343
before() 343
Capítulo 17: Roles y Permisos (Spatie) 344
Roles y permisos 345
Instalación y configuración 346
Seeder: Permisos y roles 347
Métodos para asignar: Permisos a roles, roles a permisos y usuarios, permisos y roles 348
Roles a permisos y/o usuarios 348
Permisos a roles (o roles a permisos) 349
Caso práctico 350
Verificar accesos 353
Verificar permisos y roles en controladores 353
Permisos 353
Roles 353
Verificar permisos en vistas 354
Crear un CRUD de roles 354
Crear un CRUD de permisos 360
Agregar/remover permisos a roles 365
Estructura inicial 365
Asignar permisos al role mediante un formulario 367
Remover un permiso de un role mediante un formulario 369
Asignar permisos al role mediante peticiones HTTP mediante JavaScript 371
Instalar axios 371
Crear petición por axios 372
Adaptar el método de asignación de permisos para manejar peticiones por formularios y peticiones
axios 373
Agregar un item (permiso) al listado 374
Evitar insertar permisos repetidos en el listado 375
Remover permisos del role seleccionado 377
Crear CRUD para los usuarios 379
Generar factory para usuarios 387
Gestión de roles a usuario 389
Listado de roles asignados al usuario 390
Asignar roles 391
Eliminar roles 393
Gestión de permisos a usuario 395
Listado de permisos 395
Asignar permisos a usuario 396
Remover permisos a usuario 398
Verificar accesos mediante spatie 399
Crud de posts y categorías 400
Crud de usuarios 405
Acceso de usuarios al dashboard 408
Crud de roles y permisos 410
Migrar verificación de permisos de controladores a Gate para los usuarios 413
Definir enlaces y verificación de accesos a los CRUDs en las vistas 415
Diseño 417
Capítulo 18: Relaciones en Laravel 421
Relaciones uno a uno 421
Relaciones uno a muchos 422
Relaciones muchos a muchos 423
Relaciones polimórficas 426
Caso práctico: Relación muchos a muchos 427
Caso práctico: Relación uno a muchos 433
Caso práctico: Relación uno a uno 434
Conclusión 436
Capítulo 19: Aspectos generales 438
Variables de entorno y configuraciones 438
Crear nuestras propias configuraciones 439
Crear archivos personalizados 440
Logging 441
Configurar canales para el log 441
Canales por defecto 442
Niveles de Log 443
Formateador para el log 444
Paginación Personalizada 446
Paginator 446
LengthAwarePaginator 446
Enviar correos electrónicos 447
Clase Mailable 448
Enviar correos de forma individual 450
Parámetros 450
CC y BCC/CCO 451
Enviar correos en masa 451
Helpers 451
Crear el archivo con las funciones 452
Registrar en el composer.json 452
Refrescar dependencias 452
Colecciones 453
Operaciones transaccionales en la base de datos 455
Eager loading y lazy loading 456
L
a
z
y
L
o
a
din
g
(
C
a
r
g
a
p
e
r
e
z
o
s
a
)
4
5
6
E
a
g
e
r
L
o
a
din
g
(
C
a
r
g
a
a
n
sio
s
a
)
4
5
9
C
o
n
clu
sió
n
4
6
1
M
u
t
a
d
o
r
e
s
y
a
c
c
e
s
o
r
e
s
4
6
1
M
u
t
a
d
o
r
e
s
4
6
1
A
c
c
e
s
o
r
e
s
4
6
2
L
o
c
aliz
a
tio
n
y
t
r
a
d
u
c
cio
n
e
s
4
6
3
C
a
d
e
n
a
s
d
e
t
e
x
t
o
s
p
a
r
a la
t
r
a
d
u
c
ció
n
4
6
3
P
u
blic
a
r lo
s
a
r
c
hiv
o
s
d
e idio
m
a
4
6
4
C
r
e
a
r la
s
c
a
d
e
n
a
s
d
e
t
r
a
d
u
c
ció
n
4
6
4
T
r
a
d
u
cir
m
e
n
s
aj
e
s in
t
e
r
n
o
s
al f
r
a
m
e
w
o
r
k
4
6
7
C
o
n
fig
u
r
a
r la
c
o
n
fig
u
r
a
ció
n
r
e
gio
n
al (lo
c
aliz
a
ció
n
)
4
6
7
Mid
dle
w
a
r
e
p
a
r
a
p
r
e
fij
o
d
e le
n
g
u
aj
e
e
n la
U
R
L
4
6
8
A
t
rib
u
t
o
s
4
7
0
M
a
y
ú
s
c
ula
s
4
7
0
A
t
rib
u
t
o
s
p
e
r
s
o
n
aliz
a
d
o
s
e
n
@
vit
e
4
7
1
R
e
m
o
v
e
r la
c
a
r
p
e
t
a
p
u
blic
o in
d
e
x.p
h
p
d
e la
U
R
L
e
n
L
a
r
a
v
el 4
7
2
Q
u
e
u
e
s
a
n
d
J
o
b
/
C
ola
s
y
T
r
a
b
aj
o
s
4
7
3
C
o
n
t
r
ola
d
o
r
d
e
c
ola
4
7
4
C
r
e
a
ció
n
y
e
n
v
í
o
d
e
j
o
b
s
/
t
r
a
b
aj
o
s
4
7
5
T
r
a
b
aj
o
p
a
r
a
p
r
o
c
e
s
a
r
e
m
ails
4
7
6
T
r
a
b
aj
o
p
a
r
a
p
r
o
c
e
s
a
r im
a
g
e
n
4
7
9
O
t
r
o
s
c
o
m
a
n
d
o
s
y
o
p
cio
n
e
s
ú
tile
s
4
8
1
T
r
a
b
aj
o
s
f
allid
o
s
4
8
1
A
t
rib
u
t
o
s
d
e la
cla
s
e
J
o
b
4
8
2
M
a
n
ej
o
d
e
p
á
gin
a
s
d
e
e
r
r
o
r
e
s
y
e
x
c
e
p
cio
n
e
s
4
8
3
M
a
n
ej
o
d
e
e
x
c
e
p
cio
n
e
s
4
8
3
P
e
r
s
o
n
aliz
a
ció
n
d
e
p
á
gin
a
s
d
e
e
r
r
o
r
4
8
5
E
x
c
e
p
cio
n
e
s
p
e
r
s
o
n
aliz
a
d
a
s
4
8
6
E
s
t
r
a
n
g
ula
mie
n
t
o
/
T
h
r
o
t
tlin
g
4
8
6
S
u
b
d
o
minio
s
o
m
últiple
s
d
o
minio
4
8
6
D
e
s
c
a
r
g
a
r
a
r
c
hiv
o
s
p
r
o
t
e
gid
o
s
4
8
7
P
r
e
f
e
r
e
n
cia
s
d
e
u
s
u
a
rio
s
4
8
9
B
a
n
n
e
a
r
U
s
u
a
rio
s
4
9
0
C
a
p
í
t
u
l
o
2
0
:
P
a
q
u
e
t
e
s
i
m
p
r
e
s
c
i
n
d
i
b
l
e
s
4
9
2
Sim
ple
Q
R
4
9
2
L
a
r
a
v
el E
x
c
el 4
9
3
E
x
p
o
r
t
a
r
4
9
3 Importar 494
S
E
O
e
n
L
a
r
a
v
el 4
9
5
S
E
O
To
ols
4
9
5
L
a
r
a
v
el S
E
O
4
9
6
L
a
r
a
v
el D
a
s
h
b
o
a
r
d
4
9
7
L
a
r
a
v
el N
o
c
a
p
c
h
a
4
9
7
L
a
r
a
v
el D
e
b
u
g
b
a
r
4
9
7
P
a
y
P
al 4
9
7
Cla
v
e
s
d
e
a
c
c
e
s
o
y
u
s
u
a
rio
s
d
e
p
r
u
e
b
a
4
9
8 Implementar un sencillo sistema de pagos 501 Cliente 501 Servidor 506
E
x
t
r
a: S
t
rip
e
5
1
0
C
r
e
a
r
e
n
t
o
r
n
o
d
e
P
r
u
e
b
a
5
1
1
C
r
e
a
r
c
r
e
d
e
n
ciale
s
d
e
p
r
u
e
b
a
5
1
2
C
r
e
a
r
p
r
o
d
u
c
t
o
s
d
e
p
r
u
e
b
a
5
1
3
V
u
e
S
t
rip
e
5
1
4
P
a
r
á
m
e
t
r
o
s
y
s
e
s
sio
nId
d
e
finid
o
s
e
n
el c
o
m
p
o
n
e
n
t
e
a
n
t
e
rio
r
5
1
7
S
o
b
r
e
el s
e
s
sio
nId
5
1
8
P
u
n
t
o
s im
p
o
r
t
a
n
t
e
s
d
el c
ó
dig
o
a
n
t
e
rio
r
5
1
9
L
a
r
a
v
el C
a
s
hie
r
(
S
t
rip
e
)
5
1
9 Instalación y configuración 519 API Key 520 Generar sessionID 522 Establecer el sessionID en el success_url 525 Verificar sessionId en el servidor 526 Enviar petición desde el cliente 530 Payment Intent 531 Pagos rechazados 533 Otros métodos de Laravel Cashier 533 Customers 533 Balance 534 Métodos de pago e Intenciones de cobro 535 Configurar tarjeta del cliente 535 Obtener los métodos de pago 537 Eliminar los métodos de pago 538 Crear intenciones de pago 538 Procesar intenciones de pago 539 Suscripción 541 Métodos importantes 541 Mediante el plugin de Vue Stripe 543 Configuración de la moneda 544 Configurar clave pública de manera global 544
m
o
bile
d
e
t
e
c
tlib
5
4
5
C
o
m
p
a
tibilid
a
d
c
o
n
Te
c
n
olo
g
í
a
s
E
m
e
r
g
e
n
t
e
s
5
4
5
L
a
r
a
v
e
l
F
o
r
t
i
fy
5
4
6 Instalación y configuración 547 Características 547 Verificación de usuario por emails 552
B
r
e
e
z
e
5
5
3
P
e
r
s
o
n
aliz
a
r
vis
t
a
s
d
e
a
u
t
e
n
tic
a
ció
n
e
n
B
r
e
e
z
e
5
5
4
L
a
r
a
v
el S
o
cialit
y
5
5
4
E
x
t
r
a: C
K
E
dit
o
r
5
5
4
L
a
r
a
v
el y
C
K
E
dit
o
r
5
5
5
E
m
b
e
b
e
r
C
K
E
dit
o
r
d
e
n
t
r
o
d
el fo
r
m
ula
rio
5
6
4
P
r
o
c
e
s
o
d
e
U
plo
a
d
5
6
5
Sim
ple
U
plo
a
d
A
d
a
p
t
e
r
5
6
7
C
u
s
t
o
m
A
d
a
p
t
e
r
5
7
0
E
nla
c
e
s
d
e in
t
e
r
é
s
y
e
r
r
o
r
e
s
c
o
m
u
n
e
s
y
p
á
gin
a
s
d
e
a
p
o
y
o
5
7
4
C
a
p
í
t
u
l
o
2
1
:
P
r
u
e
b
a
s
5
7
5
¿
P
o
r
q
u
é
h
a
c
e
r
p
r
u
e
b
a
s
?
5
7
5
¿
Q
u
é
p
r
o
b
a
r
?
5
7
5
P
r
u
e
b
a
s
c
o
n
P
e
s
t
/
P
H
P
U
nit
5
7
5
E
n
t
e
n
die
n
d
o la
s
p
r
u
e
b
a
s
5
7
7
P
e
ticio
n
e
s
H
T
T
P
5
8
1
R
e
c
o
m
e
n
d
a
cio
n
e
s
5
8
2
C
o
n
fig
u
r
a
r
b
a
s
e
d
e
d
a
t
o
s
p
a
r
a
p
r
u
e
b
a
s
5
8
3
P
r
u
e
b
a
d
e
s
olicit
u
d
e
s
y
r
e
s
p
u
e
s
t
a
s
H
T
T
P
5
8
6
A
pi R
e
s
t
c
o
n
P
H
P
U
nit
5
8
6
C
a
t
e
g
o
r
í
a
s
5
8
7
O
b
t
e
n
e
r
t
o
d
a
s la
s
c
a
t
e
g
o
r
í
a
s
5
8
7
O
b
t
e
n
e
r
c
a
t
e
g
o
r
í
a
p
o
r id
y
slu
g
5
8
7
C
r
e
a
r
u
n
a
t
a
r
e
a
5
8
8
E
dit
a
r
u
n
a
c
a
t
e
g
o
r
í
a
5
8
9
Elimin
a
r
u
n
a
c
a
t
e
g
o
r
í
a
5
8
9
E
r
r
o
r
e
s
d
e
v
alid
a
ció
n
5
9
0
C
a
t
e
g
o
r
í
a
s
q
u
e
n
o
e
xis
t
a
n
5
9
2
P
o
s
t
5
9
2
M
ó
d
ulo
d
e
u
s
u
a
rio
5
9
9
L
o
gin
y
g
e
n
e
r
a
r
el t
o
k
e
n
5
9
9
L
o
g
o
u
t
6
0
0
Ve
rific
a
r
el t
o
k
e
n
6
0
1
L
o
gin in
c
o
r
r
e
c
t
o
6
0
2
To
k
e
n in
c
o
r
r
e
c
t
o
6
0
2
C
o
n
s
u
mir
t
o
k
e
n
d
e
s
d
e
r
e
c
u
r
s
o
s
p
r
o
t
e
gid
o
s
6
0
3
O
r
g
a
niz
a
r
e
n
c
a
r
p
e
t
a
s
t
u
s
p
r
u
e
b
a
s
6
0
4
A
p
p
W
e
b
6
0
4
M
ó
d
ulo
d
e
u
s
u
a
rio
6
0
4
L
o
gin
6
0
5
L
o
gin in
v
alid
o
6
0
7
R
e
gis
t
r
a
r
6
0
8
R
e
gis
t
r
o in
v
álid
o
6
0
9
M
ó
d
ulo
blo
g
6
1
0
Lis
t
a
d
o
p
a
gin
a
d
o
6
1
0
D
e
t
alle
6
1
2
M
ó
d
ulo
d
a
s
h
b
o
a
r
d
6
1
5
C
R
U
D
p
a
r
a lo
s
p
o
s
t
s
6
1
5
A
u
t
e
n
tic
a
ció
n
6
1
5
Lis
t
a
d
o
6
1
6
C
r
e
a
r
6
1
7
E
r
r
o
r
e
s
d
e
v
alid
a
ció
n
e
n
c
r
e
a
r
6
1
8
E
dit
a
r
6
1
9
E
r
r
o
r
e
s
d
e
v
alid
a
ció
n
e
n
e
dit
a
r
6
2
0
Elimin
a
r
6
2
1
C
R
U
D
p
a
r
a la
s
c
a
t
e
g
o
r
í
a
s
6
2
1
C
R
U
D
p
a
r
a lo
s
r
ole
s
6
2
5
C
R
U
D
p
a
r
a lo
s
p
e
r
mis
o
s
6
2
9
C
R
U
D
p
a
r
a lo
s
u
s
u
a
rio
s
6
3
3
P
r
u
e
b
a
s
c
o
n
P
e
s
t
/
P
H
P
U
nit
6
3
7
P
r
u
e
b
a
s
U
nit
a
ria
s
c
o
n
P
e
s
t
6
3
9
¿
Q
u
é
e
s
T
D
D
?
6
6
1
C
a
p
í
t
u
l
o
2
2
:
L
a
r
a
v
e
l
a
p
r
o
d
u
c
c
i
ó
n
6
6
2 Integración con Node 662 Archivos y carpetas a publicar 663 Simplifica o descarta el archivo .env 663 Subir el proyecto al hosting 663 Terminar configuración del proyecto 665 Configurar la base de datos 665 .htaccess 666 Conclusión 666
Capítulo 1: Software necesario e instalación de las
herramientas
Laravel, al igual que otros frameworks, puede ejecutarse en diversos entornos tanto en ambiente de producción
como de desarrollo:
● Si hablamos de servidor, puedes emplear Nginx o Apache.
● Si hablamos de base de datos, puedes emplear SQLite y/o MySQL, entre otras.
● En cuanto al lenguaje de programación, tienes que emplear PHP cuya versión mínima soportará depende
de la versión que estés empleando del framework; para la versión 12 de Laravel, que es la última, sería
PHP 8.2.
Si quieres saber todas las opciones que tienes para servir Laravel y que base de datos soporta; puedes ver los
siguientes enlaces:
https://laravel.com/docs/master/deployment
https://laravel.com/docs/master/database
Este tipo de requerimientos son comunes al momento de desarrollar en Laravel y, generalmente en cada sistema
operativo tienes diversas opciones; por nombrar algunas:
● En Windows tenemos varias maneras, tanto oficiales o no; las que resaltan más serían:
○ La del hilo WSL2 Linux con Docker y Sail.
○ Laragon, que es una solución no oficial que funciona muy bien y es fácil de configurar.
● En MacOS tenemos Laravel Herd, Docker con Sail y Valet.
● En Linux tenemos Docker con Sail.
Laravel al ser un framework más complejo y completo que otros framework como el de CodeIgniter, el software
necesario es más exigente y más difícil (o al menos tedioso) de configurar y por lo tanto, requiere un mayor nivel
de conocimiento en diversos aspectos que van desde habilitar el WSL2 Linux en Windows, configurar Docker e
instalación y configuración por la terminal del sistema; algo lejos de las soluciones “next next next” que nos
hemos acostumbrados.
Recuerda que el objetivo de este libro, no es instalar las herramientas bases, ni tratar temas que no sean
directamente el framework; como desarrollador que quieres aprender a usar un framework como Laravel,
deberías de conocer las bases que sustentan al mismo (Apache, PHP…); sin embargo, se darán algunas
recomendaciones y unos resúmenes de las configuraciones disponibles o más usadas según tu SO.
Windows y MacOS
En este apartado, veremos los ambientes de desarrollo disponibles para Windows y MacOS que es el de Laravel
Herd, en el caso de Windows, también podremos usar la opción de Laragon, cual emplear, depende del lector ya
que ambas son herramientas excelentes, aunque, Laravel Herd es la opción recomendada por el equipo de
Laravel.
1
Laravel Herd
La opción recomendada y utilizada en este libro en el desarrollo de aplicaciones en MacOS y Windows es la de
Laravel Herd que viene siendo el equivalente de Laragon pero para MacOS, con Laravel Herd tenemos
disponibles un ambiente en el cual desarrollar en Laravel y podemos instalar y varias versiones de dependencias
como una base de datos (solo para las personas que paguen la versión Pro) o PHP al igual que las base de
datos; la pagina oficial viene siendo esta:
https://herd.laravel.com/
Una vez instalado, tendremos un panel como el siguiente:
Figura 1-1: Ventana de Laravel Herd en MacOS
Ahora, vamos a instalar un módulo adicional para gestionar la base de datos:
https://dbngin.com/
Una vez instalado, tendremos una ventana como la siguiente:
2
Figura 1-2: Ventana de escritorio Dbngin
También habilitados que se inicie al momento de iniciar sesión y que se anexe a la barra:
3
Figura 1-3: Ventana de barra Dbngin
Desde la ventana de la figura 1-2 o de la 1-3 podemos dar click al icono de + y crear una base de datos:
4
Figura 1-4: Crear una base de datos en Dbngin
En la cual, solamente tenemos que colocar el nombre de la base de datos (que, en el caso de Laravel, por
defecto debe ser el nombre del proyecto) y el servicio que quieras emplear, en este curso, emplearemos MySQL,
como puedes ver en la imagen 1-4, el usuario es root sin contraseña.
Una vez creada la base de datos, tenemos acceso directo al manejador que tengamos instalado, en este
ejemplo, el de Table Plus que está disponible para MacOS, Windows y Linux.
Laragon
En el caso de Windows, también podemos emplear Laragon:
https://laragon.org/
En su versión "Full".
5
En el libro, iremos cambiando entre Laragon y Laravel Herd y queda de parte del lector cuál ambiente prefiere
emplear, aunque te recomendamos que emplees Laravel Herd al ser el ambiente oficial.
Como puedes ver en la página, los propósitos de Laragon es permitir el desarrollo en aplicaciones Laravel,
aunque lo puedes emplear también para desarrollar en otros tipos de frameworks y tecnologías; tanto PHP, como
con Node y Python.
Laragon ofrece un esquema sencillo y flexible para nuestro Windows; si de casualidad, ya dispones en Windows
de XAMPP, WAMPP o similares, puedes instalarlo paralelamente a estos, ya que Laragon funciona en un
ambiente aislado al Sistema Operativo.
Sumado a esto, Laragon tiene otra ventaja interesante, que viene siendo la de generar automáticamente URLs
limpias mediante la configuración automática del virtual host o host virtuales; de tal manera que, no tenemos que
acceder como:
http://localhost/larafirststeps/public/<resto-dominio>
Así que, una vez instalado, el software, debes de tener una ventana como la siguiente:
Figura 1-5: Laragon ventana
6
Importante señalar que, debes de tener al menos PHP en su versión 8.3.3 para trabajar con Laravel 11; en mi
canal de YouTube, explico cómo puedes instalar versiones de PHP en Laragon.
Por lo demás, Laragon ya nos trae todo lo que necesitamos para empezar a trabajar en Laravel.
Desde el botón que dice "Iniciar Todo", incias tanto Apache como MySQL (lo cual debes hacer para poder
trabajar con Laravel); así que, si tienes otro ambiente LAMP habilitado en tu equipo, primero, debes de bajar
dichos servicios y luego levantar este con el mencionado botón:
Figura 1-6: Laragon ventana iniciada
Además de instalar Laragon, debes de habilitar la siguiente extensión "pdo_sqlite":
7
Figura 1-7: Habilitar pdo_sqlite en Laragon
Ya que, al momento de crear el proyecto en Laravel puede que se detenga mediante un error o al momento de
iniciar el servidor de un error como el siguiente:
could not find driver PRAGMA foreign_keys = ON;
Y es porque debes de habilitar la extensión anterior.
8
MacOS y Linux
En Mac y Linux, puedes emplear Laravel Sail, que emplea Docker; pero antes de instalar Laravel Sail, vamos a
necesitar cumplir con sus requerimientos; en este caso, tener Docker instalado; para descargar Docker, puedes
emplear la siguiente página:
https://www.docker.com/
Docker es un sistema de contenedores que es aislado al sistema operativo y por lo tanto, nos permite poder crear
en base a imágenes, el ambiente que necesita nuestra aplicación en Laravel (MySQL, PHP y demás
dependencias) para que puedan ser fácilmente administrables; no es necesario que conozcas Docker en
profundidad, ya que Laravel se encarga de la parte de la configuración.
Docker solamente lo debes de instalar si decides emplear Laravel Sail en MacOS, pero, como comentamos
anteriormente, el ambiente recomendado es el de Laravel Herd.
Composer
Otro software necesario es Composer que no es más que, un manejador de dependencias para PHP, con el
podemos instalar paquetes de terceros como librerías para trabajar con pdfs, hojas de cálculo, login y el propio
framework; y en definitiva un montón de dependencias más para habilitar nuestros sistemas; además de tener el
propio framework actualizado y sus dependencias con:
$ composer update
Composer ya viene con Laragon y Laravel Herd; así que, si estás empleando alguno de estos softwares,
no necesitas hacer nada más; si empleas otro software, para instalarlo tenemos que ir a la siguiente
página:
https://getcomposer.org/
9
Figura 1-8: Composer logo
Composer viene siendo el Manejador de Paquetes de Node (NPM) pero para PHP.
Su instalación pasa por tener PHP instalado en el equipo y ejecutar algo como:
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('sha384', 'composer-setup.php') ===
'55ce33d7678c5a611085589f1f3ddf8b3c52d662cd01d4ba75c0ee0459970c2200a51f492d557530c71c15d8db
a01eae') { echo 'Installer verified'; } else { echo 'Installer corrupt';
unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php
php -r "unlink('composer-setup.php');"
Te recomiendo encarecidamente que copies estos comandos directamente desde la web de Composer, ya que,
es probable que puedan cambiar algunos pasos; aunque los pasos pueden parecer complejos lo que hacen es lo
siguiente:
1. Descargar el instalador mediante PHP:
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
2. Verificar que el archivo descargado no este corrupto u ocurre algún problema en la descarga:
10
php -r "if (hash_file('sha384', 'composer-setup.php') ===
'906a84df04cea2aa72f40b5f787e49f22d4c2f19492ac310e8cba5b96ac8b64115ac402c8cd292b8a034825749
15d1a8') { echo 'Installer verified'; } else { echo 'Installer corrupt';
unlink('composer-setup.php'); } echo PHP_EOL;"
3 Ejecutar e instalar Composer:
$ php composer-setup.php
4 Finalmente, elimina el archivo:
$ php -r "unlink('composer-setup.php');"
Ya con tu ambiente listo, vamos a empezar a trabajar con Laravel.
Agregar composer en el PATH en MacOS
Si estás usando MacOS y escribes "composer" en la terminal, seguramente verás un error como el siguiente:
composer: command not found
Para agregar composer al PATH del sistema y poder usarlo desde cualquier ubicación en la terminal, tenemos:
mv composer.phar /usr/local/bin/composer
Esto mueve composer.phar a /usr/local/bin/ y se renombra como composer (que sigue siendo un ejecutable, no
una carpeta).
Configurar Git
Ya sea que tienes instalado Git en el sistema, en el caso de MacOS o viene instalado como parte de la solución
como en el caso de Laragon, debes de configurar git con tu nombre e email:
$ git config --global user.name "Your Name"
$ git config --global user.email "your.email@example.com"
11
Capítulo 2: Conociendo aspectos generales de
Laravel
En este capítulo, conoceremos cómo crear un proyecto en Laravel con las herramientas anteriores y como está
formado un proyecto en Laravel, sus archivos y carpetas, finalmente, trataremos aspectos más técnicos del
framework para lograr un "Hola Mundo" como el sistema de rutas y la línea de comandos conocida como Artisan.
Organización de un proyecto
Un proyecto en Laravel, puede ser muy cambiante; según la configuración que realices, así como los paquetes
que instales puedes tener más carpetas y archivos; a partir de la versión 11 de Laravel, tenemos algunos
cambios importantes que debemos de tener en cuenta; veamos las carpetas y archivos principales de un
proyecto en Laravel:
La carpeta app
La carpeta app contiene el código central de la aplicación. Esta es la carpeta central del proyecto en donde
pasaremos la mayor parte del tiempo; en este archivo están casi todas las clases de la aplicación.
La carpeta bootstrap
La carpeta de Bootstrap contiene el archivo app.php que arranca el framework; el primer archivo que se ejecuta
es el public/index.php que finalmente carga el mencionado archivo app.js. Esta carpeta también alberga una
carpeta de caché que contiene archivos generados por el framework para la optimización del rendimiento, como
los archivos de caché de rutas y servicios. Por lo general, no es necesario hacer cambios aquí.
La carpeta de config
La carpeta de config, como su nombre lo indica, contiene todos los archivos de configuración de su aplicación;
base de datos, cors, jetstream, app y muchas más.
La carpeta database
La carpeta de database contiene las migraciones de la base de datos, y los seeders. Si lo desea, también puede
usar esta carpeta para almacenar una base de datos SQLite.
La carpeta lang
La carpeta lang alberga todos los archivos de idiomas; por defecto, no viene incluida en Laravel; puedes
publicarla la carpeta en caso de que requieras usar múltiples lenguajes en tu aplicación con:
$ php artisan lang:publish
12
La carpeta public
La carpeta public contiene el archivo index.php, que es el punto de entrada para todas las solicitudes que
ingresan a su aplicación y configura la carga automática. Esta carpeta también alberga archivos que pueden ser
manejados por el navegador como imágenes, JavaScript y CSS.
La carpeta de resources
La carpeta de resources contiene sus vistas, así como sus activos sin compilar, como CSS o JavaScript.
La carpeta de routes
La carpeta de routes contiene todas las definiciones de ruta para su aplicación. De forma predeterminada, se
incluyen varios archivos de ruta con Laravel: web.php, console.php:
1. El archivo web.php contiene rutas que son empleadas para manejar la aplicación web; es decir, la que se
consume mediante el navegador; estas rutas están configuradas para proporcionar estado de sesión,
protección CSRF y cifrado de cookies.
2. El archivo channels.php es donde puedes registrar todos los canales de transmisión de eventos que
admite tu aplicación.
A partir de Laravel 11, para publicar los siguientes archivos que fueron marcados como opcionales:
1. El archivo api.php contiene las rutas para la creación de una Api Rest; estas rutas están diseñadas para
no tener estado, por lo que las solicitudes que ingresan a la aplicación a través de estas rutas deben
autenticarse mediante tokens y no tendrán acceso al estado de la sesión.
2. El archivo console.php es donde puede definir todos sus comandos de consola basados en artisan
mediante comandos.
Debemos de ejecutar los comandos de artisan:
$ php artisan install:api
Y
$ php artisan install:broadcasting
Respectivamente.
Al generar las rutas para la API, hará que se instale Laravel Sanctum, del cual hablaremos en otro capítulo:
./composer.json has been updated
Running composer update laravel/sanctum
Loading composer repositories with package information
Updating dependencies
Lock file operations: 1 install, 0 updates, 0 removals
- Locking laravel/sanctum (v4.X)
Writing lock file
Installing dependencies from lock file (including require-dev)
Package operations: 1 install, 0 updates, 0 removals
13
- Downloading laravel/sanctum (v4.X)
- Installing laravel/sanctum (v4.X): Extracting archive
La carpeta storage
La carpeta storage contiene sus registros, plantillas Blade compiladas, sesiones basadas en archivos, cachés de
archivos y otros archivos generados por el framework; esta carpeta se puede usar para almacenar cualquier
archivo generado por su aplicación.
La carpeta de tests
La carpeta de test contiene sus pruebas automatizadas; es decir, las pruebas unitarias de PHPUnit.
La carpeta de vendor
La carpeta de vendor contiene sus dependencias de Composer.
La carpeta de app
La mayor parte de la aplicación se encuentra en la carpeta de app, que como mencionamos antes, es donde
pasaremos la mayor parte de nuestro tiempo; la mayoría de las clases se encuentran en esta carpeta y podemos
definir archivos con configuraciones para distintos propósitos.
En esta carpeta, se ubican otras carpetas; vamos a explicar las principales:
La carpeta Http
La carpeta Http contiene sus controladores, middleware y solicitudes de formulario.
La carpeta de Models
La carpeta de models contiene todas las clases de modelos de Eloquent. Cada tabla de la base de datos tiene un
"Modelo" correspondiente que se utiliza para interactuar con esa tabla. Los modelos le permiten consultar datos
en sus tablas, así como insertar, actualizar y eliminar registros en la tabla.
Crear un proyecto en Laravel
En este apartado, conoceremos los mecanismos disponibles para crear un proyecto en Laravel.
En todos los sistemas operativos, tenemos dos posibilidades:
1. Instalar Laravel mediante el instalador de Laravel.
2. Instalar mediante Composer.
Ambas formas son equivalentes, pero, para instalarlo mediante el instalador de Laravel, tenemos que instalarlo
como si fuera una dependencia global a Composer y mediante el instalador de Laravel tenemos más opciones de
personalizar el proyecto antes de su creación.
14
Windows
Para crear un proyecto en Laravel con Windows, debes de abrir la terminal, en el caso de Windows con Laragon,
desde el panel de Laragon figura 1-1.
Vas a abrir tu terminal de Laragon (el botón que dice "Terminal") y te debes de posicionar sobre:
C:\laragon\www
Y a partir de aquí, tenemos dos opciones; selecciona la de tu preferencia.
Con el instalador de Laravel
Primero instalamos el instalador de Laravel con:
$ composer global require laravel/installer
En caso de que ya tengas instalado el instalador, puedes desinstalarlo e instalarlo nuevamente para actualizar el
mismo:
$ composer global remove laravel/installer
$ composer global require laravel/installer
Y luego, crear el proyecto empleando el instalador de Laravel:
$ laravel new larafirststeps
Preguntará si quieres configurar algunos de los siguientes paquetes al crear el proyecto en Laravel:
_ _
| | | |
| | __ _ _ __ __ ___ _____| |
| | / _` | '__/ _` \ \ / / _ \ |
| |___| (_| | | | (_| |\ V / __/ |
|______\__,_|_| \__,_| \_/ \___|_|
Which starter kit would you like to install?
● None
○ React
○ Vue
○ Livewire
Estas opciones las cubriremos más adelante, por lo tanto, podemos escribir "none" para que nos cree un
proyecto limpio en Laravel.
El siguiente es el framework para hacer pruebas:
15
Which testing framework do you prefer? [Pest]:
[0] Pest
[1] PHPUnit
Puedes seleccionar cualquier opción colocando 0 o 1, para este proyecto, no cubriremos el uso de las pruebas.
La siguiente pregunta es si quieres inicializar un repositorio en git colocando yes/no:
Would you like to initialize a Git repository? (yes/no) [no]:
>
La decisión queda de parte del lector, pero, en el libro colocaremos "yes" ya que emplearemos git con github para
publicar el proyecto.
Seleccione el motor de base de datos, puedes dejar cualquiera, pero en el libro usaremos MySQL.
Which database will your application use? [SQLite]:
[mysql ] MySQL
[mariadb] MariaDB
[pgsql ] PostgreSQL
[sqlite ] SQLite
[sqlsrv ] SQL Server
Colocamos un "yes" para ejecutar las migraciones:
Default database updated. Would you like to run the default database migrations? (yes/no)
[yes]:
Con Composer
La segunda opción disponible para crear un proyecto en Laravel, es sin emplear el instalador de Laravel y
empleando composer en su lugar:
$ composer create-project laravel/laravel larafirststeps
En la cual, inclusive tienes la ventaja de poder especificar una versión en especifica; por ejemplo:
$ composer create-project laravel/laravel="11.*" larafirststeps
En ambos casos tendremos como resultado tendrás una carpeta nueva que corresponde al proyecto en:
C:\laragon\www
llamada "larafirststeps"
Reinicia tu Laragon en la opción de Recargar o desde el botón de "Detener" y luego "Iniciar Todo" y en ese
momento, saltará una notificación de Windows que dice que Laragon intenta modificar el sistema (el archivo de
host de Windows); debes aceptar dicha modificación, y con eso, podrás acceder de la siguiente manera:
16
http://larafirststeps.test
Si creas el proyecto por composer, adicionalmente debes de ejecutar las migraciones:
$ php artisan migrate
El uso de las migraciones lo veremos más adelante en el libro; para ejecutar el comando anterior, recuerda que
debes de estar dentro del proyecto:
C:\laragon\www\larafirststeps
Figura 2-1: Ventana de bienvenida de Laravel
MacOS y Linux
Esta opción la debes de seguir si quieres emplear Laravel junto con Composer, empleando el asistente llamado
Laravel Sail, abres tu terminal, puedes posicionarla en cualquier parte, como escritorio o alguna ubicación que
tengas destinada para tus proyectos; y ejecutamos el comando de:
$ curl -s "https://laravel.build/larafirststeps" | bash
Este proceso demora un tiempo, mientras se descarga el proyecto:
andrescruz@Mac-mini-de-Andres Desktop % curl -s "https://laravel.build/larafirststeps" |
bash
Unable to find image 'laravelsail/php81-composer:latest' locally
17
latest: Pulling from laravelsail/php81-composer
eb9a2845ed12: Downloading 9.631MB/30.06MB
1847f78773be: Download complete
6ff48a7e6ce3: Downloading 6.986MB/86.72MB
8d3c1623fb1a: Download complete
ed88b3f807f2: Downloading 7.318MB/11.84MB
53674ff3d8e3: Waiting
c0d6d82777d8: Waiting
4a5c216bb23d: Waiting
f4a309a79847: Waiting
0c21c0241293: Waiting
a40c40f5805e: Waiting
9001901c200e: Waiting
cb38a3bbfb68: Waiting
5393bb85a813: Waiting
Una vez creado el proyecto, puede navegar hasta la carpeta de la aplicación (app) e iniciar Laravel Sail. Laravel
Sail proporciona una interfaz de línea de comandos simple para interactuar con la configuración predeterminada
de Docker de Laravel:
$ ./vendor/bin/sail up
Puedes ver más comandos útiles con Laravel Sail en:
https://laravel.com/docs/master/sail
MacOS con Laravel Herd
Si empleas Laravel Herd, al instalar la herramienta, se habrá creado una carpeta en:
/Users/<YourUser>/Herd
En donde tenemos que crear los proyectos; te posicionas la terminal en la ubicación anterior y podemos crear el
proyecto mediante el instalador de Laravel o Composer, al igual que en Windows con Laragon.
En todos los sistemas operativos, tenemos dos posibilidades:
3. Instalar Laravel mediante el instalador de Laravel.
4. Instalar mediante Composer.
Con el instalador de Laravel
Primero instalamos el instalador de Laravel con:
$ composer global require laravel/installer
En caso de que ya tengas instalado el instalador, puedes desinstalarlo e instalarlo nuevamente para actualizar el
mismo:
18
$ composer global remove laravel/installer
$ composer global require laravel/installer
Y luego, crear el proyecto empleando el instalador de Laravel:
$ laravel new larafirststeps
Preguntará si quieres configurar algunos de los siguientes paquetes al crear el proyecto en Laravel:
_ _
| | | |
| | __ _ _ __ __ ___ _____| |
| | / _` | '__/ _` \ \ / / _ \ |
| |___| (_| | | | (_| |\ V / __/ |
|______\__,_|_| \__,_| \_/ \___|_|
Which starter kit would you like to install?
● None
○ React
○ Vue
○ Livewire
Estas opciones las cubriremos más adelante, por lo tanto, podemos escribir "none" para que nos cree un
proyecto limpio en Laravel.
El siguiente es el framework para hacer pruebas:
Which testing framework do you prefer? [Pest]:
[0] Pest
[1] PHPUnit
Puedes seleccionar cualquier opción colocando 0 o 1, para este proyecto, no cubriremos el uso de las pruebas.
La siguiente pregunta es si quieres inicializar un repositorio en git colocando yes/no:
Would you like to initialize a Git repository? (yes/no) [no]:
>
La decisión queda de parte del lector, pero, en el libro colocaremos "yes" ya que emplearemos git con github para
publicar el proyecto.
Seleccione el motor de base de datos, puedes dejar cualquiera, pero en el libro usaremos MySQL.
Which database will your application use? [SQLite]:
[mysql ] MySQL
19
[mariadb] MariaDB
[pgsql ] PostgreSQL
[sqlite ] SQLite
[sqlsrv ] SQL Server
Colocamos un "yes" para ejecutar las migraciones:
Default database updated. Would you like to run the default database migrations? (yes/no)
[yes]:
Con Composer
La segunda opción disponible para crear un proyecto en Laravel, es sin emplear el instalador de Laravel y
empleando composer en su lugar:
$ composer create-project laravel/laravel larafirststeps
En la cual, inclusive tienes la ventaja de poder especificar una versión en especifica; por ejemplo:
$ composer create-project laravel/laravel="10.*" larafirststeps
Ambos casos son equivalentes y como resultado tendrás una carpeta nueva en:
/Users/<YourUser>/Herd
llamada "larafirststeps"
Con eso, podrás acceder de la siguiente manera:
http://larafirststeps.test
Ejecutar la aplicación de Laravel
Tenemos varias maneras de trabajar con Laravel como puedes ver en la documentación oficial, aunque en el libro
solo mencionaremos tres formas; con Laragon, Sail y Laravel Herd.
Laragon
Si empleas Windows y Laragon, habrás notado que Laragon nos genera una URL limpia mediante el virtual host
de manera automática para acceder a la aplicación:
<VirtualHost *:80>
DocumentRoot "C:/laragon/www/larafirststeps/public"
ServerName larafirststeps.test
ServerAlias *.larafirststeps.test
<Directory "C:/laragon/www/larafirststeps/public">
AllowOverride All
Require all granted
20
</Directory>
</VirtualHost>
Esto es un virtual host que nos autogenera Laragon y al ser autogenerados son administrados internamente pero
que puedes ver cuales tienes creados desde la aplicación de Laragon en:
Menú - Apache - site enabled
Para ejecutar la aplicación, basta con iniciar tu Laragon como mencionamos anteriormente y acceder con la URL
de tu aplicación; en el caso de este libro será:
http://larafirststeps.test/
Laravel Sail
Por supuesto, si usas otro sistema operativo como Linux o MacOS, tienes que emplear Laravel Sail; recuerda
tener abierto Docker.
$ ./vendor/bin/sail up
Recuerda que la carpeta de vendor, señalada anteriormente, es la que existe a nivel del proyecto
larafirststeps/vendor.
La primera vez que ejecutes el comando anterior, demorará un tiempo mientras se descargan las dependencias
del proyecto, se crea la imagen y en general, se prepara todo el ecosistema; finalmente, veremos en nuestro
Docker algo como lo siguiente:
Figura 2-2: Proyecto Laravel iniciado mediante Docker y Laravel Sail
21
Y una vez levantada, podrás ir a localhost desde tu navegador y mirar una pantalla como la figura 2-1.
Otro punto importante es que, una vez creado el contenedor y la aplicación en Docker, podrás iniciar o detener tu
aplicación directamente desde Docker sin necesidad de ejecutar el comando de sail up.
Laravel Herd
Si empleas Herd, al instalar Laravel Herd, se habrá creado una carpeta en:
/Users/<YourUser>/Herd
Desde la ventana de Laravel Herd, figura 1-4 damos click en "Settings", desde esta ventana también puedes ver
la ruta que va a buscar Herd para habilitar tus proyectos en Laravel; específicamente desde la pestaña de
"General":
Figura 2-3: Laravel Herd, Pestana sites
Una vez creado el proyecto como mostramos más adelante, verás la URL generada en la pestaña Site del panel
de la figura 2-3, que no es más que el nombre del proyecto con la extensión de ".test".
22
Primeros pasos con Laravel
Laravel como otros frameworks, contiene muchas funcionalidades que debemos de conocer para poder empezar
a crear nuestras aplicaciones que pasan por el MVC y extendiendo el mismo desde allí con otros esquemas.
Este apartado es fundamental, para empezar a crear nuestras primeras aplicaciones; se da por hecho que ya
conoces de manera básica para que sirven las carpetas y archivos principales en el framework.
Modo desarrollador
Empecemos por el punto más importante en este capítulo, o al menos el más básico, el de poder ver los errores
que van a suceder cuando desarrollamos la aplicación. Antes de esto, para entender la importancia de este
modo, vamos a ocasionar un error de sintaxis en nuestra aplicación para ver que sucede; en el archivo:
routes/web.php
Que tenemos:
***
Route::get('/', function () {
return view('welcome');
});
Quitemos un ";" para el view('welcome'); y al ir a nuestra app:
Figura 2-4: Error de sintaxis
Este modo de errores es muy útil cuando estamos desarrollando la aplicación; por defecto, cuando creamos un
proyecto en Laravel, ya estamos en modo desarrollador.
23
En la raíz del proyecto, tenemos un archivo llamado ".env"; el cual tiene muchas configuraciones; pero la que nos
interesa serían:
APP_NAME=Laravel
APP_ENV=local
APP_KEY=base64:2nJ3yqATJ5pIDuP2le6XruDPjgoYW/hCxc16QQLnHlI=
APP_DEBUG=true
APP_URL=http://larafirststeps.test
1. El nombre de la app.
2. El ambiente (modo desarrollo).
3. Una clave de la aplicación.
4. El modo debug (modo desarrollo).
5. La URL para acceder a nuestra app.
Fijate que, si cambias el nombre del archivo ".env", por ejemplo, le quitas el punto "env" e intentas ingresar
nuevamente a tu aplicación y manteniendo el error anterior; en este caso verás:
Figura 2-5: Error 500
Así que, este archivo permite manejar las configuraciones globales a nivel de tu aplicación; las mismas
configuraciones que se encuentran dentro de config, están aquí; ya que, generalmente este archivo se emplea en
modo desarrollo (al menos con todas estas configuraciones que aparecen por defecto) cuando pasas a modo
producción podrías prescindir de este archivo para que Laravel emplee las que tienes definidas a nivel de la
carpeta config.
24
El archivo .env, que es empleado para agregar variables de entorno; generalmente se emplea en ambiente de
desarrollo, ya que, con el mismo podemos acceder rápidamente a las configuraciones del proyecto sin necesidad
de cambiar las configuraciones que se encuentran a nivel del proyecto dentro de la carpeta config.
¿Cómo sabe Laravel qué configuración emplear, la del .env o de las de la carpeta
config?
Si revisamos algún archivo en la carpeta config; por ejemplo:
config/database.php
Verás que existen múltiples configuraciones que podemos emplear para las base de datos soportadas por
Laravel por defecto; verás que en muchas definiciones existe una función llamada env() la cual recibe dos
parámetros, una KEY (como las especificadas en nuestro ".env"; por ejemplo la de "DB_DATABASE" o
"DB_USERNAME") y un valor, el valor por defecto; es decir, que si Laravel no encuentra una la clave/key
establecida en este archivo, en el archivo .env, entonces usa la configuración por defecto.
Que la clave no exista puede deberse a dos razones:
1. Que el archivo .env no exista.
2. Que exista el archivo .env pero no exista dicha clave.
Así que, si quieres cambiar algún parámetro de tu base de datos u otro:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=testtest
DB_USERNAME=root
DB_PASSWORD=
Lo puedes hacer en el archivo .env.
Finalmente, vuelve a colocar el punto y como que generaba el error anterior, y continuemos con el siguiente
apartado.
Configurar la base de datos MySQL
En este apartado vamos a configurar la base de datos; dependiendo del sistema operativo que emplees, van a
ser diferentes los pasos.
También es importante señalar que estos pasos solamente debes de realizarlos si creastes el proyecto mediante
el instalador de Laravel y seleccionastes MySQL como base de datos o si actualizastes el proyecto para emplear
MySQL.
Si no escogiste MySQL, desde el archivo env verás:
25
.env
DB_CONNECTION=sqlite
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=laravel
# DB_USERNAME=root
# DB_PASSWORD=
Es decir, estamos empleando SQLite, la base de datos se encuentra dentro:
database\database.sqlite
Para visualizar la misma, puedes emplear alguna extensión de VSC o instalar manejadores como:
https://sqlitebrowser.org/
Si quieres cambiar a MySQL, debes de crear manualmente una base de datos en MySQL y configurar los
parámetros:
.env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=larafirststeps
DB_USERNAME=root
DB_PASSWORD=
Windows con Laragon
Como indicamos inicialmente, vamos a emplear una base de datos en MySQL o MariaDB en nuestro proyecto;
para esto, lo primero que tenemos que hacer es crear una base de datos en MySQL/MariaDB.
Para crear una base de datos, lo puedes hacer mediante la terminal; algo como:
CREATE DATABASE larafirststeps;
O el cliente que estés empleando, que viene siendo la opción recomendada; en este libro será Laragon; con el,
disponemos de uno instalado por defecto; así que, desde la opción de "Base de Datos", creamos una llamada
larafirststeps:
26
Figura 2-6: Manejador de base de datos
Para configurarla en el proyecto, colocamos las credenciales en el .env para modo desarrollo; aunque
generalmente no hay que actualizar nada:
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=larafirststeps
DB_USERNAME=root
DB_PASSWORD=
O directamente desde el archivo:
config/database.php
27
***
'mysql' => [
'driver' => 'mysql',
'url' => env('DATABASE_URL'),
'host' => env('DB_HOST', '127.0.0.1'),
'port' => env('DB_PORT', '3306'),
'database' => env('DB_DATABASE', 'larafirststeps'),
'username' => env('DB_USERNAME', 'root'),
'password' => env('DB_PASSWORD', ''),
***
Aunque aquí deberías de colocar tus configuraciones para cuando pasas a ambiente de producción.
Mac o Linux con Sail y Docker
Laravel Sail ya nos genera una base de datos, al igual que un usuario y contraseña por defecto; en nuestro .env
podrás ver la configuración a emplear:
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=larafirststeps
DB_USERNAME=sail
DB_PASSWORD=password
Importante notar que, MySQL forma parte de Docker dentro del contenedor; por lo tanto, debes de detener
cualquier instancia local que tengas a nivel del sistema operativo para evitar conflictos; por ejemplo, si empleas
Homebrew sería algo como esto:
brew services stop mysql
Para ingresar a la base de datos, puedes emplear o la terminal o la manera recomendada, sería con un
manejador; para Mac te recomiendo TablePlus:
https://tableplus.com/
Pero puedes emplear el que tu quieras; con Docker iniciado al igual que la aplicación; coloca las siguientes
credenciales:
28
Figura 2-7: TablePlus, configurar conexión a base de datos
Y luego al darle a Test o Connect para conectarse:
29
Figura 2-8: TablePlus, conexión realizada
Modelo vista controlador
El patrón MVC es uno de los muchos patrones de diseño de software que existen en la actualidad, uno de los
más empleados y del cual surgieron numerosas variantes como MTV, MVP, MVA, MVVM y un largo etc; como
puedes suponer, es un patrón escalable, adaptable y si tiene tantas variantes significa que tiene un gran
potencial; frameworks como Laravel y por supuesto CodeIgniter, lo emplean para organizar nuestro código, que
sea sencillo de mantener, adecuar cambios, mantenerlo y otros aspectos relacionados con los lineamientos que
debemos de seguir para desarrollar cualquier tipo de aplicación.
30
Laravel usa el patrón Modelo, Vista, Controlador (MVC); esto mantiene cada capa como partes separadas, pero
funciona en conjunto como un todo:
1. Los modelos administran los datos de la aplicación y ayudan a hacer cumplir las reglas comerciales
especiales que la aplicación pueda necesitar.
2. Las vistas son archivos simples, con poca o ninguna lógica, que muestran la información al usuario; están
compuesta de HTML para la parte estática y de PHP para la parte dinámica; aparte de CSS y JavaScript.
3. Los controladores actúan como un código adhesivo, ordenando datos entre la vista (o el usuario que los
está viendo) y el almacenamiento de datos, es decir, el modelo; este componente es donde generalmente
pasamos más tiempo (junto con la vista) ya que, tenemos que organizar todo lo que vamos a ver en la
vista, aplicar validaciones y demás reglas según la lógica de que programemos en nuestra aplicación.
En su forma más básica, los controladores y modelos son simplemente clases que tienen un trabajo específico
que señalamos anteriormente; pero, siempre existen procesos que podemos reutilizar y esto, lo hacemos
mediante la definición de otras clases como servicios, archivos de ayuda etc; que pueden formar parte del
núcleo, es decir, parte del framework, o pueden ser definidas por ti, como parte de la aplicación que estás
creando o por terceros, instaladas o copiadas en el framework.
Es importante señalar que, Laravel está un paso adelante del MVC ya que, ha evolucionado tanto que no es un
MVC puro por decirlo de alguna manera; podemos definir la misma lógica del MVC de diversas formas; por
ejemplo, para el controlador tenemos 3 formas de hacer lo mismo mediante rutas, controladores y componentes;
en definitiva, para probar lo anterior, basta con revisar la carpeta de Http del framework:
Figura 2-9: Carpeta HTTP, contenido
Que como hablamos anteriormente, es la empleada para que un cliente pueda consumir la aplicación mediante
HTTP; verás que, la carpeta controllers es apenas una parte parte de la carpeta Http; pero señalamos el MVC
como una parte para entender el flujo básico.
En Laravel tenemos carpetas designadas para una labor en particular; como explicamos anteriormente; nosotros
como desarrolladores, vamos a pasar la mayor parte del tiempo en la llamada /app y la carpeta de /resources
Laravel es un framework enorme, con múltiples herramientas que nos provee para crear verdaderas aplicaciones
actuales, mantenibles y escalables; en un proyecto en Laravel, aparte de poder desarrollar en el propio
framework, también podemos desarrollar en Node; por lo tanto, tenemos dos enormes mundos en un mismo
proyecto.
31
Aún así, los caminos que podemos seguir para aprender a programar en Laravel y con esto, dar los primeros
pasos.
Todo comienza con el MVC, que es el inicio de todo y es el corazón del framework; pero, al igual que ocurre con
otros frameworks, como CodeIgniter o Django, existe una capa más en la cual podemos realizar algunas
pruebas, que en este caso, sería la de las rutas:
Conociendo las rutas
Las rutas, son un esquema flexible que tenemos para vincular una URI a un proceso funcional; y este proceso
funcional, puede ser:
1. Un callback, que es una función local definida en las mismas rutas.
2. Un controlador, que es una clase aparte.
3. Un componente, que es como un controlador, pero más flexible.
Si revisamos en la carpeta de routes; veremos que existen 4 archivos:
1. api: Para definir rutas de nuestras Apis Rest.
2. channels: Para la comunicación fullduplex con los canales.
3. console: Para crear comandos con artisan.
4. web: Las rutas para la aplicación web.
El que nos interesa en este capítulo es el de web.php; el cual permite definir las rutas de nuestra aplicación web
(las que nuestro cliente consume desde el navegador).
Las rutas en Laravel son un elemento central que nos permiten enlazar controladores, como poder desencadenar
nuestros propios procesos; es decir, las rutas no necesitan de los controladores para poder presentar un
contenido; y por ende, es el primer enfoque que vamos a presentar.
Si te fijas, tenemos una ruta ya definía:
Route::get('/', function () {
return view('welcome');
});
Que como puedes suponer es la que nosotros vemos por pantalla nada más al arrancar la aplicación como la
figura 2-1.
Fíjate, que se emplea una clase llamada Route, que se importa de:
use Illuminate\Support\Facades\Route;
Que es interna a Laravel y se conocen como Facades.
Los Facades no son más que clases que nos permiten acceder a servicios propios del framework mediante
clases estáticas.
32
Finalmente, con esta clase, usamos un método llamado get(); para las rutas tenemos distintos métodos, tantos
métodos como tipo de peticiones tenemos:
● POST crear un recurso con el método post()
● GET leer un recurso o colección con el método get()
● PUT actualizar un recurso con el método put()
● PATCH actualizar un recurso con el método patch()
● DELETE eliminar un recurso con el método delete()
En este caso, empleamos una ruta de tipo get(), que conlleva a emplear a una petición de tipo GET.
El método get(), al igual que el resto de las funciones señaladas anteriormente, reciben dos parámetros:
Route::<FunctionResource>(URI, callback)
1. URI de la aplicación.
2. El callback viene siendo la función controladora, que en este caso es una función, pero puede ser la
referencia a la función de un controlador o un componente.
Y donde "FunctionResource" es la method get(), post(), put(), patch() o delete().
En el ejemplo anterior, el “/“ indica que es el root de la aplicación, que es:
http://larafirststeps.test/
O localhost si empleas MacOS o Linux mediante Docker.
En este caso, la parte funcional, viene siendo una función anónima; esta función, puede hacer cualquier cosa,
devolver un JSON, un HTML, un documento, enviar un email y un largo etc.
En este ejemplo, devuelve una vista; para devolver vistas se emplea la función de ayuda (helper) llamada view(),
la cual referencia las vistas que existen en la carpeta de:
resources/views/<Views and/or Folder>
Por defecto, solamente existe un único archivo; el llamado welcome.blade.php, y si, es el que estamos
reverenciando en la ruta anterior con:
return view('welcome');
Fíjate, que no es necesario ni indicar la ruta, ni la extensión de blade o php.
Blade hace referencia al motor de plantillas que tiene Laravel que hablaremos sobre él un poco más adelante.
Si revisas la vista de welcome.blade.php:
Verás que todo el HTML de la misma:
33
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Laravel</title>
***
En el que vemos por el navegador algo como la figura 2-1.
Así que, si creamos unas rutas más:
Route::get('/writeme', function () {
return "Contact";
});
Route::get('/contact', function () {
return "Enjoy my web";
});
Y vamos a cada una de estas páginas, respectivamente:
Figura 2-10: Ejemplo ruta 1
Y
34
Figura 2-11: Ejemplo ruta 2
Route::get('/custom', function () {
$msj2 = "Msj from server *-*";
$data = ['msj2' => $msj2, "age" => 15];
return view('custom', $data);
});
views/cursom.blade.php
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Document</title>
</head>
<body>
<p>{{ $msj2 }}</p>
<p>{{ $age }}</p>
</body>
</html>
35
Figura 2-12: Ejemplo ruta 3
Rutas con nombre
Otra configuración que no puede faltar en tu aplicación, es el uso de rutas con nombre; como indica su nombre,
le permite definir un nombre a una ruta.
***
Route::get('/contact', function () {
return "Enjoy my web";
})->name('welcome');
Para eso se emplea una función llamada route() a la cual se le indica el nombre; para emplearla en la vista:
<a href="{{ route('welcome') }}">Welcome</a>
Esto es particularmente útil, ya que, puedes cambiar la URI de la ruta, agruparla o aplicarle cualquier otra
configuración, pero, mientras tengas el nombre definido en la ruta y uses este nombre para referenciarla, Laravel
va a actualizarla automáticamente.
Artisan la línea de comandos
Laravel dispone de una línea de comandos (CLI) sencilla y muy potente conocida como artisan; artisan no es
más que un archivo que se ubica en la raíz de nuestro proyecto con el nombre de "artisan" y permite ejecutar una
serie de comandos preestablecidos; por supuesto, podemos extender los comandos que nos ofrece el propio
framework programando comandos propios; pero este es otro tema; en definitiva, podemos dividir los comandos
que podemos emplear en tres grupos:
Comandos para generar archivos:
1. Crear migraciones, de esto hablaremos en otro capítulo, pero no son más que archivos que guardan la
estructura de una tabla que el framework mapeara a la base de datos.
2. Generar seeds o semillas para datos de prueba.
3. Generar controladores y otros tipos de archivos.
36
Comandos para manejar procesos:
1. Levantar un servidor de desarrollo, por si no quieres emplear Apache u otros servidores soportados por el
framework.
2. Ejecutar o devolver migraciones.
3. Limpiar caches.
4. Manejar la base de datos.
5. Ejecutar las migraciones y seeds.
Comandos para obtener información del proyecto:
1. Listado de comandos.
2. Listado de las rutas del proyecto.
Entre otros comandos que puedes ver ejecutando a nivel del proyecto:
$ php artisan
Si estás usando Laravel Sail, y no tienes PHP 8 instalado a nivel del sistema operativo; debes de ejecutar:
$ ./vendor/bin/sail artisan
Y esto es importante de señalar, ya que, sería la forma que debes de interactuar con artisan en caso de no tener
PHP 8 a nivel de sistema.
Comandos más empleados
Para que tengas una lista de los comandos; te recomiendo que la copies y la leas algunas veces al dia y te
familiarices con estos comandos que son los más empleados al momento de desarrollar en Laravel:
1. php artisan make:controller: Para crear controladores.
2. php artisan make:migration: Para generar un archivo de migración.
3. php artisan migrate: Para generar una migración y relacionados como el rollback para devolver las
migraciones.
4. php artisan routes: Para ver las rutas de la aplicación.
No te preocupes si no comprendes el propósito de estas funciones, más adelante veremos en detalle el
funcionamiento de cada uno de estos elementos.
37
Capítulo 3: Rutas, controladores y vistas
Las vistas forman parte de nuestro MVC y es la capa de presentación, de mostrar el contenido ya generado a
nuestro usuario; esto es usualmente una página en HTML, pero puede ser un documento como PDF, una
imagen, un video, etc; pasando por alto las referencias a documentos y similares, que serían casos especiales.
Las vistas en Laravel no son más que archivos PHPs que el framework traduce a una página HTML que es la
presentada al usuario final mediante el navegador que es el que realiza la consulta inicialmente, tal cual vimos
anteriormente con el caso de las rutas.
En Laravel, las vistas son especiales ya que, usualmente no son vistas PHPs comunes, si no, tienen un motor de
plantilla que no es más que un mecanismo que nos permite hacer lo mismo que hacemos en PHP (imprimir,
valores, llamar a funciones, mezclar PHP con HTML, etc) pero de una manera más limpia, más mantenible y más
sencilla que usar directamente PHP; para poder usar el motor de plantillas, tenemos que agregar la extensión
.blade antes de colocar la de .php; por ejemplo:
welcome.blade.php
Si no quisiéramos emplear el motor de blade:
welcome.php
Simplemente removemos el .blade del nombre.
Usualmente siempre usamos blade, ya que no hay una razón para no emplearlo.
Rutas y vistas
En este capítulo, vamos a presentar la manera más sencilla de mostrar una vista (es decir, que podamos ver algo
por la pantalla del navegador) y esto es empleando las rutas en combinación con las vistas que forman parte de
nuestro MVC; como comentamos al inicio del curso; Laravel no es un framework MVC puro, si no, tiene
agregados o variantes en los cuales existen varias formas de hacer lo mismo; y esta es una de estas variantes.
Caso práctico
Vamos a crear una nueva ruta:
Route::get('/contact', function () {
return view('contact');
})->name('contact');
Si vamos al navegador:
38
Figura 3-1 Página de contacto
Verás el mensaje que imprimimos anteriormente; puedes hacer exactamente lo mismo empleando el echo en vez
del return. Vamos a crear una vista asociada:
resources\views\contact.blade.php
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="ie=edge">
<title>Document</title>
</head>
<body>
<h1>Contact</h1>
</body>
</html>
La referenciamos en la ruta:
Route::get('/contact2', function () {
return view('contact2');
})->name('contact2');
Y si vamos al navegador:
39
Figura 3-2 Segunda página de contacto
Verás la representación del código anterior.
Pase de parámetros
Para pasar parámetros a la vista; basta con indicar a la función de view() un segundo parámetro que debe ser un
array en el cual colocamos la pareja de key y value del mismo:
Route::get('/contact', function () {
$name = 'Andres'
return view('contact',['name'=>$name]);
})->name('contact');
1. La key del array va a ser empleada por Laravel de manera interna para construir una variable, que es la
que podemos usar en la vista.
2. El valor/value del array, será el valor que tendrá asignada la variable generada anteriormente.
Así que, para consumir la variable anterior, tenemos:
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="ie=edge">
<title>Document</title>
</head>
<body>
<h1>Contact</h1>
<p>{{$name}}</p>
</body>
</html>
40
O
***
<p><?= $name ?></p>
***
El código PHP anterior, viene siendo PHP básico, pero qué pasa con blade; en blade, para imprimir valores,
podemos usar la siguiente sintaxis:
***
<p>{{$name}}</p>
***
Que claro está es mucho más limpia y sencilla que la anterior.
Si necesitas pasar más variables, lo colocas como un elemento más en tu array de datos:
***
return view("web/contact", ["name" => $name,"var2" => $other,...]);
***
En general, puede pasar cualquier cosa que puedas almacenar en una variable como strings, números, objetos,
etc.
Redirecciones
Muchas veces necesitamos mandar de una página a otra, ya sea porqué la página que está intentando ingresar
el usuario no está disponible, o porqué la función en sí no devuelve una vista; por ejemplo, los procesos que se
encargan de procesar los datos recibidos por formulario, usualmente no devuelven una vista; para hacer las
redirecciones tenemos tres formas.
Con esta función podemos redireccionar con la URI:
return redirect("/post/create"); // redirecciona a la vista dado el URI
Con esta función podemos redireccionar con la ruta con nombre:
return redirect()->route("post.create");
Con esta función podemos redireccionar con una ruta con nombre, como el caso anterior, pero es más corto la
sintaxis; esta función es nueva a partir de la versión 9 de Laravel:
return to_route("post.index"); // redirecciona a una ruta con nombre, igual a la anterior
pero es el shortcut
41
Directivas en Laravel para blade (vistas)
Las Directivas en Laravel proporcionan atajos convenientes para estructuras de control de PHP comunes, como
sentencias condicionales y bucles. Estos accesos directos brindan una forma muy limpia y concisa de trabajar
con estructuras de control de PHP.
Así que, las directivas no son más que un conjunto de funciones que podemos emplear para hacer lo mismo que
hacemos con PHP, pero, de una manera más limpia, tenemos directivas para muchas cosas, como incluir vistas,
crear layouts, condicionales y ciclos; por nombrar algunas.
Directiva if
Puede construir sentencias if utilizando las directivas @if, @elseif, @else y @endif. Estas directivas funcionan
de manera idéntica a sus contrapartes de PHP:
@if($name !== "Andres Cruz")
Es true
@else
No es true
@endif
Directiva foreach
Desde la función de view(), puedes pasar cualquier cosa, cualquier cosa que pueda estar almacenada en un
array:
@foreach($array as $a)
<div class="box item">
<p>{{$a}}</p>
</div>
@endforeach
Por supuesto, existen muchas más directivas basadas en los casos anteriores, y como recomendación general,
visita la página oficial de Laravel para obtener la referencia completa:
https://laravel.com/docs/master/blade#blade-directives
Ordenar vistas en carpetas
La idea principal de emplear frameworks, es que, podamos tener nuestro código organizado y mantenible, que
usualmente estos dos aspectos van de la mano; y un elemento que nos ayuda a tal fin, son las carpetas; con
esto, podemos organizar nuestro código en secciones o bloques y esto es algo bastante útil cuando por ejemplo
queremos trabajar en módulos, submódulos, secciones o la denominación que prefieras.
Suponte que, tenemos un módulo de gestión; en el cual todas sus vistas van a estar en:
views/dashboard
42
Y otro módulo para el usuario final, para la parte de consumo de estos datos de gestión, que podría estar en:
views/web
Por supuesto, los nombres son completamente personalizables y dependen de ti.
Como idea para que entiendas la lógica anterior, suponte un Blog; en un Blog, tenemos un módulo de gestión
para administrar las publicaciones, categorías, etiquetas, usuarios, etc; y un módulo para el usuario final, en el
cual ve estos posts publicados.
La misma idea, la puedes llevar a una web de películas, libros, tiendas, entre otras.
Caso práctico
Así que, podemos mover la vista que creamos anteriormente:
views/contact.blade.php
A
views/web/contact.blade.php
Y desde nuestra ruta, lo único que hacemos es agregar la referencia a la nueva o nuevas carpetas:
Route::get('/escribeme', function () {
$name = "Andrés Cruz";
return view("web/contact", ["name" => $name]);
});
Layout o vista maestra
Blade, también permite la herencia de vistas o plantillas; por lo tanto, en un solo lugar, podemos tener una
estructura base que podemos reutilizar fácilmente a lo largo de un módulo o la aplicación.
Un layout luce de la siguiente manera:
resources\views\master.blade.php
// tulayout.blade.php
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="ie=edge">
<title>Dashboard</title>
</head>
43
<body>
@yield('content')
<div>
@yield('morecontent')
</div>
</body>
</html>
Con la directiva yield, podemos definir las secciones que van a tener el contenido dinámico; ya que, la idea es
que podamos reutilizar esta plantilla a lo largo de la aplicación o módulo; por ejemplo, desde una vista cualquiera
de blade:
//vistacualquiera.blade.php
@extends('dashboard.layout')
@section('content')
<h1>Contenido principal</h1>
<p>Más contenido</p>
@endsection
@section('morecontent')
Más contenido
@endsection
● Para indicar la vista madre o layout que queremos usar, indicamos la directiva de extends.
● Para definir cada uno de los yields, indicamos el contenido en un bloque de section.
La misma lógica, la empleas en cada una de las vistas.
Vistas y controladores
Ya aprendimos a trabajar con las rutas y vistas, y vimos parte de su enorme potencial y organización; usar vistas
con las rutas, aunque es posible, es recomendado si la operación que vamos a realizar es extremadamente
sencilla e independiente de otras funciones; por ejemplo:
Si quieres una página informativa, contacto de la empresa (sin formulario), un "acerca de"; que son vistas que, de
por sí, son netamente informativas y no están ligadas hacia otra página.
Pero si tenemos un CRUD, procesos de generar PDFs, cargar archivos, página de login; generar JSON o
similares, entre otros, empleamos los controladores, que ofrecen un enfoque más modular y con mejores
prestaciones u opciones para realizar diversas operaciones como:
● Procesar formularios.
● Inyectar dependencias.
● Devolver JSON.
● Heredar de otras clases.
Entre otras.
44
Los controladores son la otra capa de nuestro MVC que están destinados a manejar la conexión entre el modelo,
que es la fuente de datos, y la vista, que es la capa de presentación para el usuario.
Para crear un controlador, podemos usar artisan, es decir, nuestra línea de comandos, y para eso tenemos el
comando de:
$ php artisan make:controller
En el cual, si queremos ver que opciones tenemos:
$ php artisan make:controller -h
-h es el shortcut, de —help, que, en definitiva, es la opción de ayuda (puedes usar este comodín para cualquier
otro comando en Laravel).
Tendremos una salida como la siguiente:
Description:
Create a new controller class
Usage:
make:controller [options] [--] <name>
Arguments:
name The name of the class
Options:
--api Exclude the create and edit methods from the controller.
--type=TYPE Manually specify the controller stub file to use.
--force Create the class even if the controller already exists
-i, --invokable Generate a single method, invokable controller class.
-m, --model[=MODEL] Generate a resource controller for the given model.
-p, --parent[=PARENT] Generate a nested resource controller class.
-r, --resource Generate a resource controller class.
-R, --requests Generate FormRequest classes for store and update.
--test Generate an accompanying PHPUnit test for the Controller
--pest Generate an accompanying Pest test for the Controller
-h, --help Display help for the given command. When no command is given
display help for the list command
-q, --quiet Do not output any message
-V, --version Display this application version
--ansi|--no-ansi Force (or disable --no-ansi) ANSI output
-n, --no-interaction Do not ask any interactive question
--env[=ENV] The environment the command should run under
-v|vv|vvv, --verbose Increase the verbosity of messages: 1 for normal output, 2 for
more verbose output and 3 for debug
45
Si estás usando Laravel Sail, y no tienes PHP 8 instalado a nivel del sistema operativo; debes de ejecutar:
$ ./vendor/bin/sail artisan make:controller -h
Y hacer lo mismo con el resto de los comandos que veas a partir de ahora.
En la cual vemos que le podemos pasar opciones y un nombre.
Tenemos muchas opciones, pero, las que generalmente usamos son 3:
1. Crear un controlador básico: php artisan make:controller TuControlador
2. Crear un controlador de tipo recurso: php artisan make:controller TuControlador -r
3. Crear un controlador (de tipo recurso) y su modelo: php artisan make:controller -r TuControlador -m
TuModelo
Inclusive, podemos indicar una carpeta para almacenar tus controladores:
$ php artisan make:controller -r php artisan make:controller -r TuCarpeta(s)/TuControlador
-m TuModelo
Que por supuesto, puedes combinar a tu antojo o necesidades las opciones presentadas.
Ahora, veamos algunos controladores de ejemplo:
$ php artisan make:controller TuControlador
Otra opción interesante, es que, podemos organizar nuestros controladores en carpetas; y esto está directamente
relacionado con lo que hablamos anteriormente en que este tipo de componentes los podemos registrar dentro
de carpetas:
$ php artisan make:controller Test/TuControlador
Caso práctico
Vamos a crear un controlador:
$ php artisan make:controller Test/TuControlador
En el cual, vamos a tener el siguiente cuerpo:
<?php
namespace App\Http\Controllers\Test;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
46
class TuControlador extends Controller
{
//
}
De momento, solamente vamos a imprimir una vista:
public function index()
{
return view('test.custom');
}
Y su ruta:
Route::get('test', [TuControlador::class, 'index']);
Verás un resultado similar al mostrado por las rutas; por lo demás, puedes pasar parámetros como vimos
anteriormente.
Rutas de tipo CRUD (recurso)
Como hablamos anteriormente, las rutas se encuentran definidas en el archivo en:
routes/web.php
En este lugar, podemos definir nuestras rutas de tipo CRUD para un recurso en particular que queramos
administrar; en este caso, posts o publicaciones:
Route::get('post', [PostController::class, 'index']);
Route::get('post/{post}', [PostController::class, 'show']);
Route::get('post/create', [PostController::class, 'create']);
Route::get('post/{post}/edit', [PostController::class, 'edit']);
Route::post('post', [PostController::class, 'store']);
Route::put('post/{post}', [PostController::class, 'update']);
Route::delete('post/{post}', [PostController::class, 'destroy']);
En donde, sin importar el tipo de función, recibe dos argumentos:
1. El segmento, que no es más que un trozo o token de la URL, y que forma parte de URI de la aplicación o
la URL completa y permite a un cliente acceder a los recursos destinados.
2. El controlador asociado para procesar esta ruta, del controlador hablaremos un poco más adelante; pero
en esencia, permiten realizar las tareas programadas al acceder al segmento anterior.
En las rutas anteriores también puedes ver unos argumentos (posts es este ejemplo) esto no es más que datos
que pasamos para que el controlador pueda realizar la tarea destinada y que pueden ser de distintos tipos; el
más común es un identificador de tipo entero para mostrar detalles del elemento especificado; de los argumentos
lo hablamos un poco más adelante en el libro.
47
Cada tipo de función anterior, está asociado a su correspondiente tipo de petición; es decir, la función de
get(), permite procesar peticiones de tipo GET, y así para el resto de las funciones.
El método GET se utiliza para recuperar datos del servidor. Este es un método de solo lectura, por lo que no tiene
riesgo de mutar o corromper los datos. Por ejemplo, si llamamos al método get() en nuestra aplicación,
obtendremos una lista de todas las tareas pendientes.
El método POST envía datos al servidor y crea un nuevo recurso. Por ejemplo, si llamamos al método post() con
los datos a nuestra aplicación, creamos una nueva tarea.
El método PUT se usa con mayor frecuencia para actualizar un recurso existente. Si desea actualizar un recurso
específico (que viene con un URI específico), puede llamar al método PUT a ese URI de recurso con el cuerpo
de la solicitud que contiene la nueva versión completa del recurso que está tratando de actualizar. Por ejemplo, si
llamamos al método PUT con los datos a nuestra aplicación incluyendo el identificador, editamos una tarea
existente.
El método PATCH es muy similar al método PUT porque también modifica un recurso existente. La diferencia es
que para el método PUT, el cuerpo de la solicitud contiene la nueva versión completa, mientras que para el
método PATCH, el cuerpo de la solicitud solo necesita contener los cambios específicos al recurso. Por ejemplo,
si llamamos al método PATCH con los datos que queremos actualizar a nuestra aplicación incluyendo el
identificador, editamos una tarea existente.
El método DELETE se utiliza para eliminar un recurso especificado por su URI. Por ejemplo, si llamamos al
método DELETE con el identificador, editamos una tarea existente.
Como mostramos en el código anterior, para crear una ruta, sin importar el tipo, usamos la clase Route, que ya
se encuentra definida en dicho archivo:
use Illuminate\Support\Facades\Route;
Seguido del tipo de petición que quieras realizar:
$routes::tipo
Que puede ser GET, POST, PUT, PATCH o DELETE.
Es importante notar que una ruta como la siguiente:
Route::get('post', [PostController::class, 'index']);
Nos permite especificar qué ruta va a ser procesada por el controlador, componente o de manera interna
mediante un callback:
1. Específicamente 'post' es una parte de la URL o fragmento de la URL para acceder a un recurso (es la
URI única que no se debe de repetir en otra ruta empleando el mismo tipo de método; es decir, puede
existir la URI de 'post' en múltiples rutas, pero tienen que ser de tipos distintos, get, post, put...).
48
2. y 'PostController::class' es el controlador que se encargará de procesar este recurso; como un
controlador es una clase, y una clase en PHP puede tener muchas funciones, aquí también especificamos
cual es la función que va a procesar dicha ruta; en este caso se llama como ''index''.
Argumentos en las rutas
Muchas veces queremos pasar argumentos en las rutas; esto es muy común cuando visitas páginas vez un
identificador en la URL de la página; por ejemplo, en mi blog, cuando entras a una publicación verás que aparece
un identificador como:
https://www.desarrollolibre.net/blog/laravel/curso-laravel-de-cero-e-integracion-con-boopstrap-y-vue
En este caso esto se conoce como slug o URL limpia, pero este identificador lo puedes definir de distintas
formas, aunque tradicionalmente es un número entero (la PK o clave primaria de nuestra base de datos, en estos
casos, definimos rutas como:
Route::get('blog/laravel/{slug}', [BlogController::class, 'detail']);
Y en el método llamada detail() del controlador BlogController:
public function detail(String $slug)
{
***
}
Se que aun no hemos vistos los controladores, pero de momento suponte que es una función, que tenemos
definida en alguna parte de nuestra aplicación; lo importante es que entiendas como mapear estos parámetros;
por ejemplo, si tienes más parámetros:
Route::get('blog/{category}/{slug}', [BlogController::class, 'detail']);
public function detail($category,$slug)
{
***
}
Trabajando con las rutas
Para una aplicación típica de tipo CRUD en la cual nos interesa poder Crear, Leer, Actualizar y Eliminar
elementos (recuerda que las siglas de estas operaciones vienen del inglés Create, Read, Update y Delete
respectivamente), necesitamos definir varias rutas para tal fin; para cada una de estas operaciones vamos a
necesitar crear formas de acceso distintas, y esto, lo conseguimos empleando las rutas.
En Laravel, tenemos unas estructuras fijas en las rutas ya definidas por defecto que es la estructura
recomendada para usar sobre las rutas de tipo CRUD.
De manera ejemplificada, vamos a trabajar con un supuesto modelo de posts para realizar las operaciones
CRUD como la que mostramos anteriormente; vamos a analizar esa estructura un poco más.
49
Crear elementos:
Para crear elementos, en este caso una película, vamos a necesitar dos rutas, la primera sería para pintar el
formulario de creación; una ruta como:
Route::get('<URICrear>', [<Controlador::FuncionCrear>::class, '<FuncionCrear>']);
Que en el esquema de las rutas en Laravel sería:
Route::get('post/create', [PostController::class, 'create']);
Como puedes ver, según el propósito de cada tipo de método, el que mejor se ajusta para este es un método de
tipo get(), ya que, solamente queremos obtener datos, sin cambiar nuestro modelo de datos (entiéndase crear,
actualizar o borrar) es netamente pintar un formulario así que la petición es de tipo GET.
Nada impide que puedas personalizar tu ruta como:
Route::get('post-crear', [PostController::class, 'crear']);
O como mejor consideres, pero, la estructura mostrada inicialmente, es la que usa el framework por defecto para
definir sus rutas y por ende, para seguir las prácticas que nos recomienda Laravel, decidimos emplear esta:
Route::get('post/create', [PostController::class, 'create']);
A nivel de URL:
http://larafirststeps.test/post/create (get)
Ahora, nos falta una segunda ruta para recibir el formulario, los datos que coloca nuestro usuario en el formulario
HTML que luego envía, procesa la aplicación y finalmente poder crear nuestro elemento (en este ejemplo un
post), en este caso, como queremos cambiar el modelo de datos, específicamente crear un elemento,
colocamos una ruta de tipo POST:
Route::post('post', [PostController::class, 'store']);
A nivel de URL:
http://larafirststeps.test/post (post)
Leer elementos:
Para leer los elementos vamos a necesitar dos rutas, una para el listado en la cual tenemos todos los registros
(en nuestro ejemplo, películas) y otra para el detalle de un elemento por vez.
En el caso de un listado para nuestros posts, lo podemos hacer de la siguiente manera:
Route::get('post', [PostController::class, 'index']);
50
A nivel de URL:
http://larafirststeps.test/post (get)
Ahora, para ver el detalle de una película, le tenemos que pasar el id o identificador de la película que queremos
ver, entiéndase la PK o clave primaria, así que, pasamos una ruta con argumento, en donde el argumento será la
PK:
Route::get('post/{post}', [PostController::class, 'show']);
A nivel de URL:
http://larafirststeps.test/post/1 (get)
Donde 1 es un identificador cualquiera.
Actualizar elementos:
Para actualizar nuestros elementos, es un caso similar al de crear, vamos a necesitar dos rutas, una para pintar
el HTML del formulario y otra para procesar la data de nuestro usuario; un punto importante es que, al editar
trabajamos sobre registros existentes; específicamente, un registro que queremos editar; así que, tanto para
pintar el formulario como para procesar el formulario necesitamos el identificador del elemento y esto lo recibimos
por la URL.
Ruta de tipo GET de la función de pintar el formulario:
Route::get('post/{post}/edit', [PostController::class, 'edit']);
A nivel de URL:
http://larafirststeps.test/dashboard/post/1/edit (get)
Ruta de la función de tipo PUT para procesar el formulario.
Aquí empleamos la petición de tipo PUT en vez de la de PATCH ya que usualmente en el CRUD modificamos
todo el registro, ahora bien, las peticiones PUT o PATCH no son soportadas por el API de HTML; en este caso el
uso de los formularios, son las que estarían dirigidas estas peticiones, por lo tanto, en Laravel tenemos que
emplear un decorador mediante en la vista (una directiva de blade) pero de esto nos ocupamos luego; de
momento, con que conozcas como esta armada la ruta es más que suficiente:
http://larafirststeps.test/post/1/edit
http://larafirststeps.test/post/1 (PUT o PATCH)
51
Borrar un elemento:
Finalmente, para borrar un elemento, solamente necesitamos un identificador y la petición de tipo DELETE; aquí
pasa lo mismo con el caso de las peticiones PUT y PATCH, no son soportadas por el API de HTML, así que,
tienes que emplear el decorador:
Route::deletete('post/{post}', [PostController::class, 'destroy']);
Ya con esto, presentamos el esquema de las distintas funciones que podemos emplear para definir nuestras
rutas para cada uno de los tipos de peticiones más utilizados; por supuesto, que todavía falta conocer cómo se
manejan las rutas juntos con los controladores; pero, esto lo veremos más adelante.
Rutas de tipo recurso
Como es común emplea procesos CRUD para todos, en Laravel tenemos un tipo de función que nos permite
unificar estas siete rutas que vimos anteriormente en una sola función, y se conocen como, rutas de tipo recurso:
Route::resource('post', PostController::class);
Esa ruta, es exactamente lo mismo que definamos las rutas de la siguiente manera:
// listado
Route::get('post', [PostController::class, 'index']);
// detalle
Route::get('post/{post}', [PostController::class, 'show']);
// crear
Route::get('post/create', [PostController::class, 'create']);
Route::post('post', [PostController::class, 'store']);
// actualizar
Route::get('post/{post}/edit', [PostController::class, 'edit']);
Route::put('post/{post}', [PostController::class, 'update']);
// borrar
Route::destroy('post/{post}', [PostController::class, 'destroy']);
Con esto, como puedes apreciar si ejecutas un comando de artisan:
$ php artisan route:list
Veras:
GET|HEAD post ........ post.index › PostController@index
POST post ........ post.store › PostController@store
GET|HEAD post/create post.create › PostController@create
GET|HEAD post/{post} ... post.show › PostController@show
52
PUT|PATCH post/{post} post.update › PostController@update
DELETE post/{post} post.destroy › PostController@dest…
GET|HEAD post/{post}/edit
Función de compact
Tenemos una forma reducida de pasar los datos a la vista; en vez de usar algo como:
public function index(){
$posts = Post::paginate(2);
return view('dashboard.post.index',['posts' => $posts));
}
Podemos usar la función de ayuda llamada compact():
public function index(){
$posts = Post::paginate(2);
return view('dashboard.post.index', compact('posts'));
}
Como puedes ver, no es necesario definir el array y con esto, el valor de la key, ya que el valor de la key se va a
mapear automáticamente con el nombre de la variable(s) de la función.
53
Capítulo 4: Migraciones
Ya sabemos cómo trabajar con los controladores de manera básica, pero, no hemos podido enlazar con los
modelos ya que, el modelo es la entrada a la base de datos, específicamente una tabla; es decir, en nuestro
modelo llamado Post (en singular) necesitamos una tabla llamada posts (en plural), lo de colocar los nombres en
plural en singular son simplemente buenas prácticas.
Inicialmente en el libro configuramos la base de datos para nuestro proyecto y ahora, es momento de usarla,
nosotros no nos conectamos directamente a las tablas de la base de datos, la creación de tablas de manera
directa no es recomendada al trabajar con el framework y prácticamente cualquier framework web moderno del
lado del servidor en PHP, Node, Python y vale contar, siguen este mismo principio de evitar que los
desarrolladores interactúen con la base de datos, y esto es genial por dos puntos:
1. Nos permite trabajar con la base de datos como un proceso más.
2. Nos permite un mejor desempeño al momento de trabajar en equipo.
Antes de explicar los dos puntos en detalle, vamos a mencionar cuál es la herramienta empleada para lograr tal
hazaña... Se conocen como migraciones, que no son más que un sistema de control para las tablas.
En definitiva, para empezar a trabajar con los modelos, necesitamos las tablas en la base de datos, y para
trabajar con las tablas, necesitamos las migraciones; comencemos por las migraciones.
Migraciones
Las migraciones son un sistema que nos permiten generar tablas en base a archivos PHPs (clases) una clase
por cada tabla en nuestra base de datos que nosotros definimos; mediante las migraciones (entiéndase clases)
podemos, o crear nuevas tablas, o modificar las existentes; una migración luce como la siguiente:
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
/**
* Run the migrations.
*
* @return void
*/
public function up(): void
{
Schema::create('categories', function (Blueprint $table) {
$table->id();
$table->timestamps();
});
54
}
/**
* Reverse the migrations.
*
* @return void
*/
public function down(): void
{
Schema::dropIfExists('categories');
}
};
Explicación del código anterior
Lo primero que podemos notar es que, tenemos un new class y heredamos de una clase Migration, que es
interna al framework y permite usar esta clase como migración; en cuando al new class, es una característica de
PHP que nos permite crear clases anónimas que es particularmente útil para evitar conflictos entre clases; no
necesitamos un nombre para esta clase ya que, no la vamos a referenciar en ninguna parte, estas clases son
empleadas de manera interna para manejar las migraciones y más nada.
Las migraciones constan de dos partes, por una parte tenemos el método de up(), en donde aplicamos las
operaciones que queremos realizar sobre la base de datos:
1. Crear una tabla.
2. Modificar una tabla existente, agregando/removiendo columnas y/o índices.
También existe un método llamada down(), la cual es usada para revertir los cambios realizados en el método
up(); es decir, si en el método de up() creamos una tabla, en down() la removemos, si en up() creamos una o
varias columnas, en down() removemos esa misma columna o columnas. Esto se debe a que el sistema de
migraciones nos permite tanto ejecutarlas, como revertir las operaciones anteriores.
Puedes ver que en el método de up(), definimos el esquema, el cual, dado el nombre de la tabla, podemos
agregar/remover columnas; por defecto, ya Laravel nos define una estructura básica, en la cual:
● Tenemos una columna para la PK $table->id()
● Tenemos las columnas para las fechas de creación y actualización $table->timestamps()
Algunas operaciones para crear columnas son:
1. id() para generar una columna llamada id, autoincremental, bigInteger y con la relación de clave primaria
o primary key.
2. string() para indicar una columna de tipo varchar; recibe dos atributos, el nombre y la longitud.
3. timestamps() crea dos columnas de tipo timestamps, una para la fecha de creación del registro, y la otra
para la fecha de actualización.
4. foreignId() este método nos permite crear la clave de tipo foránea; recibe un parámetro de manera
obligatoria, con la cual indicamos el nombre del campo; este método puede recibir más parámetros para
indicar las relaciones pertinentes, pero, si respetamos las convenciones de nombres de Laravel, no sería
necesario.
5. text() permite crear una columna de tipo text, recibe un parámetro, con el cual indicamos el nombre de la
columna.
55
6. enum() permite crear una columna de tipo enum (seleccionable) y recibe dos parámetros, el nombre de la
columna, y los valores seleccionables presentados mediante un array.
7. onDelete() indica el comportamiento que van a tener los registros al ser eliminados en una relación
foránea.
A las cuales, puedes personalizar mediante modificadores; algunos de los más comunes:
1. unsigned() para indicar que va a ser de tipo UNSIGNED.
2. nullable() para indicar que pueden ser nulos.
3. constrained() para crear el constrained/referencia a la tabla, usualmente se usa en conjunto con la
columna de tipo foreignId().
Por aqui tienes acceso a la documentación oficial, para más detalles:
https://laravel.com/docs/master/migrations#creating-columns
Al final, las migraciones no son más que archivos que definen una estructura, que con un comando, reflejamos su
estructura en la base de datos en una tabla; por lo tanto, una (o varias) migración, define una tabla en la base de
datos.
Por ejemplo, una migración luce como la siguiente:
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
/**
* Run the migrations.
*
* @return void
*/
public function up(): void
{
Schema::create('categories', function (Blueprint $table) {
$table->id();
$table->string('title', 500);
$table->string('slug', 500);
$table->timestamps();
});
}
/**
* Reverse the migrations.
*
56
* @return void
*/
public function down(): void
{
Schema::dropIfExists('categories');
}
};
En la cual, indicamos una columna para el id, título, slug y fechas.
Crear una migración
Para crear una migración, tenemos el siguiente comando:
$ php artisan make:migration <NombreMigracion>
Al cual, le podemos definir una estructura mediante un conjunto de funciones como explicamos anteriormente.
Las migraciones se ubican en la carpeta de:
database/migrations
Y es importante señalar patrones como:
create_<tabla>_table
O para modificar una tabla existente:
add_<operacion>_<tabla>_table
Ya que, si respetas estos patrones al momento de crear la migración; Laravel auto completará parte del código,
como el nombre de la tabla y las columnas de id y fechas; es decir, si no indicamos estos patrones:
// *** php artisan make:migration tablita
<?php
return new class extends Migration
{
public function up(): void
{
//
}
public function down(): void
{
//
}
};
57
Y si indicamos un patrón:
// *** php artisan make:migration createCategoriesTable
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
public function up(): void
{
Schema::create('categories', function (Blueprint $table) {
$table->id();
$table->timestamps();
});
}
public function down(): void
{
Schema::dropIfExists('categories');
}
};
Ejecutar la migración
Para tener nuestras tablas, necesitamos ejecutar nuestras migraciones, así que, vamos a empezar a trabajar con
las mismas.
Para ejecutar las migraciones:
$ php artisan migrate
Caso práctico
Vamos a crear ahora, una migración para las categorías:
$ php artisan make:migration createCategoriesTable
Con la siguiente estructura:
<?php
58
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
/**
* Run the migrations.
*
* @return void
*/
public function up(): void
{
Schema::create('categories', function (Blueprint $table) {
$table->id();
$table->string('title', 500);
$table->string('slug', 500);
$table->timestamps();
});
}
/**
* Reverse the migrations.
*
* @return void
*/
public function down(): void
{
Schema::dropIfExists('categories');
}
};
Y para los posts:
$ php artisan make:migration createPostsTable
Con la siguiente estructura:
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
59
{
/**
* Run the migrations.
*
* @return void
*/
public function up(): void
{
Schema::create('posts', function (Blueprint $table) {
$table->id();
$table->string('title', 500);
$table->string('slug', 500);
$table->text('description')->nullable();
$table->text('content')->nullable();
$table->string('image')->nullable();
$table->enum('posted', ['yes', 'not'])->default('not');
$table->timestamps();
$table->foreignId('category_id')->constrained()
->onDelete('cascade');
});
}
/**
* Reverse the migrations.
*
* @return void
*/
public function down(): void
{
Schema::dropIfExists('posts');
}
};
Explicación del código anterior
En la migración de los posts, creamos una columna llamada category_id, que como puedes suponer, es
empleada para manejar la relación de tipo foránea con las categorías; ya Laravel sabe de manera automática
como relacionar la columna de category_id con una tabla llamada categorías (en plural), ya que, estamos
empleando la convención de nombres de Laravel; pero, en caso de que hiciera falta, también puedes indicar la
tabla:
$table->foreignId('category_id')->constrained('categories');
Por lo demás, definimos una serie de columnas de tipo texto y una con un enum para manejar el estado.
Importante notar que, el orden en el cual definimos las migraciones es fundamental, ya que, en el orden en el
cual se encuentran:
60
Figura 4-1: Migraciones iniciales
Es el orden en el cual se van a ejecutar. Al ejecutar las migraciones:
$ php artisan migrate
Veremos en nuestra base de datos las tablas creadas.
Tips para tus migraciones
Va a ser el orden en el cual se encuentran definidas; por lo tanto, es común que las migraciones tengan
relaciones entre ellas; y si, intentas ejecutar una migración que tiene una relación foránea con otra relación que
aún no ha sido ejecutada, tendrás un error. Para ejemplificar esto; supón que, tenemos en nuestra carpeta de
migraciones del proyecto:
Figura 4-2: Migraciones del proyecto
61
Que es el orden inverso que tenemos originalmente presentando en el apartado anterior (la migración de post y
categoría); suponiendo que, no tengamos tablas en la base de datos del proyecto y si intentamos ejecutar las
migraciones con:
$ php artisan migrate
Verás que ocurre el error justamente cuando Laravel intenta crear la migración de post:
***
Migrating: 2024_04_17_100827_create_posts_table
SQLSTATE[HY000]: General error: 1215 Cannot add foreign key constraint (SQL: alter table
`posts` add constraint `posts_category_id_foreign` foreign key (`category_id`) references
`categories` (`id`) on delete cascade)
at
C:\laragon\www\larafirststeps\vendor\laravel\framework\src\Illuminate\Database\Connection.p
hp:712
711▕ catch (Exception $e) {
➜ 712▕ throw new QueryException(
713▕ $query, $this->prepareBindings($bindings), $e
714▕ );
715▕ }
716▕ }
1
C:\laragon\www\larafirststeps\vendor\laravel\framework\src\Illuminate\Database\Connection.p
hp:501
PDOException::("SQLSTATE[HY000]: General error: 1215 Cannot add foreign key
constraint")
2
C:\laragon\www\larafirststeps\vendor\laravel\framework\src\Illuminate\Database\Connection.p
hp:501
PDOStatement::execute()
Ya que, al estar antes definida que la de categorías, no puedes crear la relación foránea.
Flujo de las migraciones
En este apartado vamos a ver otros comandos y opciones útiles sobre las migraciones:
Revertir las migraciones (rollback)
Muchas veces nos damos cuenta de que faltó agregar una columna, o necesitamos cambiar la definición de la
misma, en estos casos tenemos dos escenarios posibles:
1. Crear otra migración para agregar estas columnas o cambiar las existentes.
62
2. Hacer un rollback de las migraciones y hacer los cambios en la migración que define dicha tabla que
queremos cambiar y ejecutar nuevamente las migraciones.
En definitiva, hacemos un rollback cuando no va bien y tenemos que hacer correcciones; el comando es:
migrate:rollback
Y si lo ejecutas, verás que se revierten justamente las migraciones que hayas ejecutado; es decir:
● Si cuando ejecutamos el comando de migrate se ejecutaron las migraciones para las categorías y posts,
entonces, al ejecutar el rollback, se revirtieron ambas migraciones.
● Si cuando ejecutamos el comando de migrate se ejecutó solamente una de las migraciones, ya sea la de
categorías o posts, entonces, al ejecutar el rollback, se habrá revertido justamente la migración que se
ejecutó en el último migrate.
Todo esto depende del número de lote que se crea al momento de ejecutar las migraciones:
Figura 4-3: Migraciones ejecutadas y número de batch
Este número es de carácter incremental, como ocurre cuando vas insertando nuevos registros en una tabla y se
van generando los IDs/PKs de manera incremental:
63
Figura 4-4: Número de batch
Esta tabla es tomada en cuenta por el framework para saber que migraciones ha ejecutado, y tener el control del
flujo en base al número de lote.
Importante notar que, si tenemos muchas migraciones y las mismas ya fueron ejecutadas, muchas veces no es
posible hacer rollback hasta llegar a la migración que quieres editar; y esto es, para evitar revertir todas las
migraciones que anteceden a esta; por lo tanto, una solución común es la de generar una nueva migración para
indicar estos cambios.
Por ejemplo, suponte que tenemos las siguientes migraciones:
64
Figura 4-5: Migraciones de ejemplo
Y deseamos cambiar la migración de posts, que es una de las primeras como puedes evidencias en la imagen
anterior; en estos casos, es muy complicado revertir todas tus migraciones hasta llegar a la de posts, para
agregar la columna o columnas para luego ejecutar todas las migraciones nuevamente; lo que podemos hacer
para evitar esto, es crear otra migración como:
// add_extra_campo_to_posts_table
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
/**
* Run the migrations.
*
* @return void
*/
public function up(): void
{
Schema::table('posts', function (Blueprint $table) {
$table->string('extra_campo', 255)->nullable();
});
}
/**
* Reverse the migrations.
*
* @return void
*/
public function down(): void
{
Schema::table('posts', function (Blueprint $table) {
$table->dropColumn('extra_campo');
});
}
}
Que aplique los cambios sobre posts y la ejecutamos, para reflejar los cambios en la base de datos.
Ya que, recuerda que las migraciones las empleamos para dos propósitos, crear tablas, y alterar las tablas ya
existentes con nuevas columnas, índices o removiendo estos.
65
Refrescar la base de datos
Este comando es una combinación entre el rollback y el migrate, ya que, Laravel lo que hace es hacer un
rollback de todas las migraciones, para volverlas a ejecutar:
$ php artisan migrate:refresh
Si intentas ejecutar un:
$ php artisan migrate
Verás que, en este punto, Laravel no va ejecutar nuevamente las migraciones (devuelve "Nothing to migrate."), ya
que, ya fueron ejecutadas; pero, si quieres hacer una ejecución fresca, tenemos el comando de:
$ php artisan migrate:refresh
Y verás que, primero hace un rollback y luego un migrate:
Migrating: 2014_10_12_000000_create_users_table
Migrated: 2014_10_12_000000_create_users_table (45.73ms)
Migrating: 2014_10_12_100000_create_password_resets_table
Migrated: 2014_10_12_100000_create_password_resets_table (59.01ms)
Migrating: 2019_08_19_000000_create_failed_jobs_table
Migrated: 2019_08_19_000000_create_failed_jobs_table (38.28ms)
Migrating: 2019_12_14_000001_create_personal_access_tokens_table
Migrated: 2019_12_14_000001_create_personal_access_tokens_table (62.86ms)
Migrating: 2024_02_19_164133_create_categories_table
Migrated: 2024_02_19_164133_create_categories_table (22.17ms)
Migrating: 2024_02_19_164258_create_posts_table
Migrated: 2024_02_19_164258_create_posts_table (62.28ms)
Código fuente del capítulo:
https://github.com/libredesarrollo/book-course-laravel-base-11/releases/tag/v0.1
66
Capítulo 5: MVC y CRUD
En este capítulo vamos a tratar el MVC de Laravel y explicar la comunicación básica entre el controlador con el
modelo y la vista; importante notar que, todavía no vamos a trabajar con el CRUD, ya que, antes necesitamos
conocer este flujo y algunas configuraciones sobre los modelos.
Vamos a crear un controlador para administrar los posts:
$ php artisan make:controller -r Dashboard/PostController -m Post
Con este comando, generamos un controlador (ver el namespace para ubicar el controlador):
<?php
namespace App\Http\Controllers\Dashboard;
use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
class PostController extends Controller
{
/**
* Display a listing of the resource.
*
* @return \Illuminate\Http\Response
*/
public function index()
{
//
}
/**
* Show the form for creating a new resource.
*
* @return \Illuminate\Http\Response
*/
public function create()
{
//
}
/**
* Store a newly created resource in storage.
*
* @param \Illuminate\Http\Request $request
67
* @return \Illuminate\Http\Response
*/
public function store(Request $request)
{
//
}
/**
* Display the specified resource.
*
* @param \App\Models\Post $post
* @return \Illuminate\Http\Response
*/
public function show(Post $post)
{
//
}
/**
* Show the form for editing the specified resource.
*
* @param \App\Models\Post $post
* @return \Illuminate\Http\Response
*/
public function edit(Post $post)
{
//
}
/**
* Update the specified resource in storage.
*
* @param \Illuminate\Http\Request $request
* @param \App\Models\Post $post
* @return \Illuminate\Http\Response
*/
public function update(Request $request, Post $post)
{
//
}
/**
* Remove the specified resource from storage.
*
* @param \App\Models\Post $post
* @return \Illuminate\Http\Response
68
*/
public function destroy(Post $post)
{
//
}
}
Y un modelo en:
App/Models
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Post extends Model
{
use HasFactory;
}
Seguramente, artisan, dará un mensaje como el siguiente:
App\Models\Post model does not exist. Do you want to generate it? (yes/no) [yes]
Que indica que el modelo llamado Post no existe y si quieres generarlo; al cual le damos un sí (yes):
$ yes
Fíjate que, con el comando anterior, creamos el PostController.php sobre una carpeta Dashboard y creamos un
modelo llamado Post; gracias a que se indicó el modelo, en nuestro controlador, en vez de tener referencias a un
id para las operaciones de detalle, editar y eliminar:
public function show(int $id): Response
{
//
}
function edit(int $id): Response
{
//
}
public function destroy(int $id): RedirectResponse
{
//
}
69
Tenemos referencia al post, que es el elemento administrable.
Crea la ruta de tipo resources:
use App\Http\Controllers\Dashboard\PostController;
// ***
Route::resource('post', PostController::class);
En el método de index(), vamos a realizar algunas pruebas sobre nuestro modelo. Pero antes de esto, vamos a
conocer como podemos realizar las operaciones CRUD con nuestro modelo:
● Crear - Post::create(<datos>)
● Leer
○ Detalle - Post::find(<PK>)
○ Listado - Post::get()
● Actualizar $post->update(<datos>)
● Eliminar - $post->delete()
A la final, es tomar nuestro modelo, que hereda de Model, lo que significa, que es una clase modelo y con ella
tenemos de gratis múltiples funciones que podemos hacer para comunicarnos con la base de datos como las que
vimos anteriormente; es importante notar que, estas son solamente algunas operaciones, las más comunes, pero
tenemos muchísimas más:
https://laravel.com/docs/master/eloquent
También recuerda que, los modelos son la única capa que contamos para comunicarnos con la base de datos; lo
cual lo hace excelente, ya que, con esto podemos hacer independiente la base de datos con la cual estemos
trabajando, por ejemplo, MySQL, MariaDB, PSQL, SQL Server… del proyecto; podemos cambiar de una base de
datos a otra fácilmente y con pocas configuraciones.
Caso práctico
En este apartado, vamos a ver las operaciones de tipo CRUD en la base de datos mediante los modelos.
Crear un registro
Finalmente, si queremos crear un post:
public function index()
{
return Post::create(
['title' => "test",
'slug' => "test",
'content' => "test",
'category_id' => 1,
'description' => "test",
'posted' => "not",
70
'image' => "test"]
);
}
Es importante que la categoría con ID de 1 exista; si no, verás un error como el siguiente:
SQLSTATE[23000]: Integrity constraint violation: 1452 Cannot add or update a child row: a
foreign key constraint fails (`testlara10`.`posts`, CONSTRAINT `posts_category_id_foreign`
FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
Puedes crear la categoría desde la base de datos.
Si vamos al navegador:
http://larafirststeps.test/post
Verás que, ocurre un error:
Add [title] to fillable property to allow mass assignment on [App\Models\Post].
En primera instancia, el error puede parecer extraño, pero lo que está ocurriendo es que, falta definir una
configuración extra sobre nuestro modelo; debemos de decirle a Laravel que columnas son las que podrás ser
gestionadas para nuestros posts; es decir, crear o actualizar; esto es particularmente útil ya que, puedes tener
columnas que no quieres que sean manipuladas por el framework, que manejan información delicada, para hacer
debug, de control, o seguridad.
Suponte que tienes un modelo de usuarios con una columna de role, cuyo role puede ser o administrador o
regular, administrador en la aplicación solamente hay uno, que es el que creas directamente en la base de datos;
por lo tanto, la columna de los roles no vas a querer que sea administrada por el framework y esto es, para evitar
posibles vulnerabilidades en la cual un usuario malicioso pueda explotar alguna vulnerabilidad de la aplicación en
la cual se cree o administre un usuario y pueda cambiar el role del usuario por dicho medio; esto, por dar un
ejemplo.
Volviendo a los campos o columnas administrables, para definirlas tenemos que definir una propiedad protegida
llamada fillable en la base de datos, que no es más que un array que define los campos que son “rellenables”:
protected $fillable = ['title', 'slug', 'content', 'category_id', 'description', 'posted',
'image'];
Ya con esto si ingresamos nuevamente a la página:
{
"title": "test",
"slug": "test",
"content": "test",
"category_id": 1,
71
"description": "test",
"posted": "not",
"image": "test",
"updated_at": "2024-03-02T19:43:42.000000Z",
"created_at": "2024-03-02T19:43:42.000000Z",
"id": 1
}
Veremos que se creó correctamente; desde el navegador, veremos un error como el siguiente:
App\Http\Controllers\Dashboard\PostController::index(): Return value must be of type
Illuminate\Http\Response, App\Models\Post returned
Y esto es por el tipo que debemos de devolver; siguiendo el esquema de cliente/servidor el cliente, desde el
navegador, realiza una consulta a nuestra aplicación, en donde, la consulta es procesada por un controlador; la
consulta, es el request; por ejemplo:
use Illuminate\Http\Request;
public function store(Request $request): {
}
El siguiente paso, es que el servidor, devuelva una respuesta lo cual es hecho desde el mismo controlador que
es el que recibe la petición; esta respuesta, en Laravel es del tipo:
use Illuminate\Http\Response;
class PostController extends Controller
{
public function index(): Response
{
}
***
}
Lo cual es diferente a la operación que estamos retornando que, en el ejemplo anterior, es un post; y por eso la
excepción.
Actualizar un registro
Para actualizar un post, basta con emplear el método update() sobre el post que queremos editar e indicamos
los campos:
public function index()
72
{
$post = POST::find(1);
return $post->update(
[
'title' => "test new",
'slug' => "test",
'content' => "test",
'category_id' => 1,
'description' => "test",
'posted' => "not",
'image' => "test"
]
);
}
Si revisamos en la base de datos, verás que se actualizó el registro.
Para obtener todos los registros:
public function index()
{
return POST::get();
}
En este caso para que puedas apreciar el formato devuelto la recomendación es que tengas al menos dos posts
en la base de datos; basta con ejecutar un par de veces más el código que definimos en el apartado de crear un
post:
[
{
"id": 1,
"title": "test new",
"slug": "test",
****
"created_at": "2024-02-26T21:35:59.000000Z",
"updated_at": "2024-03-02T19:47:18.000000Z",
"category_id": 1
},
{
"id": 2,
"title": "test",
"slug": "test",
"created_at": "2024-02-23T15:19:35.000000Z",
"updated_at": "2024-02-26T18:11:42.000000Z",
"category_id": 1
},
73
{
****
]
Para obtener un solo post, usamos el método de find() la cual recibe el id del elemento que queremos encontrar:
public function index()
{
return POST::find(1);
}
Eliminar un registro
Finalmente, para eliminar un post, dado el post, lo eliminamos con el método de delete().
public function index()
{
$post = POST::find(1);
return $post->delete();
}
Tipos devueltos en los métodos de los controladores
Cada una de los métodos controladores, están destinadas para que realicen una operación en particular; como
iremos viendo en los siguientes capítulos; verás que el tipo devuelto por cada método varía según esta operación
que debe de realizar; por ejemplo:
● Response, Devolver una respuesta como por ejemplo una vista, o JSON.
● RedirectResponse, Devolver una redirección, estos tipos de retornos se emplean en funciones las cuales
no es necesario que regrese una vista (como en el caso del método de index, que debe de devolver un
listado) si no, operaciones como la de crear, actualizar y eliminar.
● View, Devolver una vista.
app\Http\Controllers\Dashboard\PostController.php
<?php
namespace App\Http\Controllers\Dashboard;
use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
class PostController extends Controller
{
/**
74
* Display a listing of the resource.
*/
public function index(): Response
{
return Post::create(
[
'title' => "test",
'slug' => "test",
'content' => "test",
'category_id' => 1,
'description' => "test",
'posted' => "not",
'image' => "test"
]
);
}
/**
* Show the form for creating a new resource.
*/
public function create(): Response
{
//
}
/**
* Store a newly created resource in storage.
*/
public function store(Request $request): RedirectResponse
{
//
}
/**
* Display the specified resource.
*/
public function show(Post $post): Response
{
//
}
/**
* Show the form for editing the specified resource.
*/
public function edit(Post $post): Response
{
75
//
}
/**
* Update the specified resource in storage.
*/
public function update(Request $request, Post $post): RedirectResponse
{
//
}
/**
* Remove the specified resource from storage.
*/
public function destroy(Post $post): RedirectResponse
{
//
}
}
Relaciones foráneas
Antes de seguir, al tener los posts una relación de tipo foránea con las categorías, crearemos un par de
categorías para poder trabajar de manera manual en la base de datos:
Figura 5-1: Relaciones de categorías
Como vimos en el capítulo anterior con las migraciones, creamos una relación foránea entre las categorías y los
posts, en este caso, es una relación de uno a muchos en donde:
1. Un post solamente puede tener una categoría.
2. Una categoría puede estar asignada a ninguno o muchos posts.
Esto hace que si tenemos en la tabla posts algo como:
76
Figura 5-2: Categorías de ejemplo
En esencia, dos posts pertenecen a la categoría uno, y tres posts pertenecen a la categoría dos; si desde el post
uno, preguntamos por la categoría acorde a la relación que creamos anteriormente.
Para reflejar este comportamiento en los modelos, basta con indicar la relación de tipo belongsTo() a la entidad
que guarde la relación; en este caso, la de post es la que guarda la relación de categorías; la relación directa:
class Post extends Model
{
use HasFactory;
protected $fillable = ['title', 'slug', 'content', 'category_id', 'description',
'posted', 'image'];
public function category()
{
return $this->belongsTo(Category::class);
}
}
Y la de categorías, podemos obtener la relación inversa; para eso, en este caso, empleamos la relación de
hasMany(); vamos a crear un modelo para las categorías:
app\Models\Category.php
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
77
class Category extends Model
{
use HasFactory;
protected $fillable = ['title', 'slug'];
public function posts()
{
return $this->hasMany(Post::class);
}
}
Como puedes ver, al final la relación no es más que, un método con cualquier nombre, que establece la relación
correspondiente.
Por supuesto, en Laravel podemos manejar más tipos de relaciones:
https://laravel.com/docs/master/eloquent-relationships
Por ejemplo, la de One to One o uno a uno, es similar a esta, pero en vez de definir la relación de tipo HasMany,
tenemos la de HasOne.
Te pudieras preguntar ¿en qué escenarios pueden definir el tipo de relación One To One? en muchos casos, pero
por dar un ejemplo, suponte una relación de usuario y dirección de vivienda, en donde un usuario solamente
puede tener una dirección principal de vivienda; en este caso, en cualquiera de las entidades puedes registrar la
relación, es decir, usuario puede registrar la relación foránea con la dirección, o la dirección puede guardar la
relación foránea con el usuario:
class User extends Authenticatable
{
***
protected $fillable = [
'name', 'surname', 'email', 'password', 'address_id',
];
}
class Address extends Authenticatable
{
***
protected $fillable = [
'address', 'country', ***,
];
}
O
class User extends Authenticatable
78
{
***
protected $fillable = [
'name', 'surname', 'email', 'password',
];
}
class Address extends Authenticatable
{
***
protected $fillable = [
'address', 'country', ***, 'user_id'
];
}
Ya que, es una relación de uno a uno; por lo demás, los mismos criterios que vimos anteriormente para la
relación de tipo One to Many, los puedes aplicar con el de One to One, pero indicando la relación de HasOne en
vez de la de OneMany.
Ahora, con el nombre que hayas definido para indicar la relación (el nombre del método en tu modelo), lo
empleamos desde la relación, como si fuera un atributo de la clase:
dd(Post::find(1)->category);
Obtenemos:
App\Models\Category {#1220 ▼
***
#attributes: array:5 [▼
"id" => 1
"title" => "Cate 1"
"slug" => "cate-1"
"created_at" => "2024-02-23 11:52:11"
"updated_at" => "2024-02-23 11:52:03"
]
Y si preguntamos por la relación inversa:
dd(Category::find(1)->posts);
Obtenemos:
Illuminate\Database\Eloquent\Collection {#1219 ▼
#items: array:2 [▼
0 => App\Models\Post
1 => App\Models\Post
]
#escapeWhenCastingToString: false
79
}
Y así de fácil resulta trabajar con las relaciones en Laravel; no hay necesidad de hacer joins o consultas
adicionales para traer la tabla relacional (categorías) desde la relación principal (posts), todo lo tenemos en una
simple referencia a la relación que creamos en el modelo.
Código fuente del capítulo:
https://github.com/libredesarrollo/book-course-laravel-base-11/releases/tag/v0.2
80
Capítulo 6: CRUD y formularios
En este capítulo finalmente vamos a construir una verdadera aplicación funcional; ya al conocer cómo funcionan
los componentes básicos de Laravel, que pasan por conocer su entorno, crear un proyecto y conocer las rutas
vistas y migraciones, ya estamos listos para crear nuestro primer CRUD en el cual nos enfocaremos en la parte
funcional y nada en la parte de estilos.
Para este capítulo, vamos a emplear el controlador llamado PostController.php de tipo recurso que creamos
anteriormente y los modelos; vamos a comenzar por el primer proceso, que sería el de crear.
Crear
Vamos a comenzar con el proceso de crear un registro y su método controladora:
app\Http\Controllers\Dashboard\PostController.php
use Illuminate\View\View;
***
public function create(): View
{
$categories = Category::pluck('id', 'title');
// dd($categories);
return view('dashboard.post.create', compact('categories'));
}
Explicación del código anterior
● Con el modelo de categorías, obtenemos todos los registros; el método de pluck() permite obtener los
registros de la base de datos y retornarlos en un array de tipo clave valor: array [ "Cate 1" => 1 "Cate 2"
=> 2 ], pudiéramos emplear una condición get en su lugar (Category::get() pero, al no interesarnos
obtener la relación para las categorías teniendo un proceso más complejo, empleamos el método
anterior).
● Cambiamos el retorno del método de un Response a un View ya que, devolvemos una vista, el uso del
indicar el tipo de retorno en los controladores es opcional y puedes prescindir del mismo si no deseas
usarlo.
● Por lo demás, creamos una vista en la ubicación anterior a la cual, le pasamos las categorías.
Pudiéramos emplear el método de get(), pero con el método de pluck() es más sencillo el manejo de los datos
para cuando lo único que necesitamos es la pareja de clave y valor, en este caso, justamente la clave es para
construir el listado empleando el SELECT de HTML.
Vamos a crear una vista dentro de:
dashboard/post/create.blade.php
Con el siguiente contenido:
81
<form action="" method="post">
<label for="">Title</label>
<input type="text" name="title">
<label for="">Slug</label>
<input type="text" name="slug">
<label for="">Content</label>
<textarea name="content"></textarea>
<label for="">Category</label>
<select name="category_id">
</select>
<label for="">Description</label>
<textarea name="description"></textarea>
<label for="">Posted</label>
<select name="posted">
<option value="not">Not</option>
<option value="yes">Yes</option>
</select>
<button type="submit">Send</button>
</form>
Explicación del código anterior
● Creamos un campo de formulario por cada columna que tenemos definida en la base de datos para
nuestros Post:
○ Título
○ Sug
○ Descripción
○ Contenido
○ Categoría
○ Posteado
Indicando cada tipo de campo al cual corresponda; si tenemos campos para colocar mucho texto, empleamos
TEXTAREAs, si tenemos una relación foránea, empleamos un campo de selección.
● Para el campo de categorías, iteramos cada una de las categorías que le pasamos a la vista, que en este
caso son todas las que tenemos en la base de datos, colocando como value, el identificador de la
categoría.
● Usamos una ruta con nombre, en este caso para indicar el action del formulario, que sería la de store
que debes enviar vía una petición POST.
Y el método para procesar la petición, y a posterior, almacenar los datos en la base de datos.
82
En nuestro método de store(), vamos a definir el proceso de creación de un post, que basta con indicar mediante
nuestro modelo, el método llamada create() que recibe un array con los datos que nosotros vamos a insertar:
app\Http\Controllers\Dashboard\PostController.php
public function store(Request $request): RedirectResponse
{
Post::create($request->all());
return to_route("post.index");
}
Con el $request->all() obtenemos en un array, todos los datos del formulario (puedes hacer uso del método dd()
para más información).
Si ingresamos a la página para crear rellenamos los campos y le damos a enviar:
83
Figura 6-1: Formulario para creación post.
Veremos una excepción de tipo 419:
84
Figura 6-2: Página sin token CSRF
Esto pasa, ya que, Laravel para evitar el cross-site request forgery o falsificación de petición en sitios cruzados
emplea un token que genera e inyecta en los formularios; así que, para poder emplear este token, basta con
emplear la siguiente directiva:
@csrf
<label for="">Título</label>
<input type="text" name="title" value="{{ old('title', $post->title) }}">
Y si probamos nuevamente, se habrá creado un registro en la base de datos (consulta tu base de datos):
Figura 6-3: Registro creado en la base de datos
El Cross-site request forgery es un tipo de ataque que consiste en suplantar la identidad; en este caso en
particular; sería que un usuario envíe desde un sitio web ajeno al de nuestra aplicación, una solicitud a la función
que se encarga de procesar nuestro formulario.
Validar datos
Otro proceso fundamental, en los procesos CRUDs es la validación de los datos, para evitar trabajar con
formatos que no son aceptables por la aplicación según el modelo de datos que estemos empleando; para eso,
tenemos algunos mecanismos, pero todos pasan por definir una serie de reglas:
https://laravel.com/docs/master/validation#available-validation-rules
Reglas como indicar que, son requeridos, tipo entero, longitud mínima o máxima, etc; una validación típica, luce
de la siguiente manera:
"title" => "required|min:5|max:500",
85
● En la cual colocamos una key, que corresponde con el nombre del campo de formulario.
● Y las reglas de validación, en caso de que coloques más de una, como en el caso anterior, se separan por
pipes.
Aparte de las reglas, necesitamos una estructura para poder indicar las reglas anteriores; para esto, tenemos
varios casos, podemos hacerlo en un archivo con una clase aparte, que viene siendo lo más utilizado, o un
proceso más manual, indicando las validaciones directamente en el controlador.
Para ejemplificar lo anterior, podemos aplicar validaciones, desde el request, de la siguiente manera:
$request->validate([
"title" => "required|min:5|max:500",
"slug" => "required|min:5|max:500",
"content" => "required|min:7",
"category_id" => "required|integer",
"description" => "required|min:7",
"posted" => "required"
]);
Como mencionamos anteriormente, existen algunas variantes para esto, pero en esencia, definimos las reglas de
validación con una pareja clave/valor:
1. Donde la clave, es el nombre del campo en el formulario.
2. El valor, son las reglas de validación.
Caso práctico
En este apartado, veremos las distintas maneras que tenemos de trabajar con las validaciones, algunas son más
manuales que otras y otras requieren de más lógica y falta de organización, pero ganan en personalización del
proceso que quieras desencadenar una vez aplicadas las validaciones.
Validaciones en el controlador mediante el request
Vamos a aplicar las siguientes reglas para crear un post:
app\Http\Controllers\Dashboard\PostController.php
public function store(Request $request): RedirectResponse
{
$request->validate([
"title" => "required|min:5|max:500",
"slug" => "required|min:5|max:500",
"content" => "required|min:7",
"category_id" => "required|integer",
"description" => "required|min:7",
"posted" => "required"
]
86
);
***
}
Explicación del código anterior
Como explicamos antes, en base a la pareja de clave y valor, especificamos el campo y las validaciones sobre el
mismo respectivamente:
● Con required indicamos que todos los campos sean requeridos.
● Con el min y max indicamos la longitud mínima y máxima respectivamente para los campos.
● Con el integer que el valor sea entero, que, en este caso, como son las PKs de las categorías, siempre
deben de ser números enteros.
Como puedes ver, desde el request, podemos emplear un método llamado validate() que espera recibir un
array, un array sobre las validaciones que queremos aplicar.
Finalmente, si enviamos un formulario que no cumpla con las reglas definidas anteriormente:
Figura 6-4: Formulario para crear
Veremos que de manera automática nos redirecciona a la vista de crear; es decir, nuestro formulario, ya que es el
comportamiento por defecto; el motivo de esta redirección, es que en esta pantalla debemos de mostrar los
errores productos de la validación del formulario; de esto nos ocupamos luego; y por supuesto, intenta pasar un
formulario con los datos válidos, verás que todo sigue funcionando perfectamente, es decir, crea el registro en la
base de datos.
87
Validaciones en el controlador mediante el request, segunda forma
Existe otro método para aplicar validaciones localmente en el controlador que nos ofrece más control que el caso
anterior, es decir, no va a realizar la redirección a la vista de formulario de manera automática:
use Illuminate\Support\Facades\Validator;
***
$validated = Validator::make($request->all(), ["title" =>
"required|min:5|max:500",
"slug" => "required|min:5|max:500",
"content" => "required|min:7",
"category_id" => "required|integer",
"description" => "required|min:7",
"posted" => "required"
]);
);
Con esta propiedad, podemos obtener el estado de la validación; es decir, si pasó o no las validaciones:
dd($validated->fails());
// ***
false
O los errores:
dd($validated->errors());
// ***
^ Illuminate\Support\MessageBag {#1211 ▼
#messages: array:5 [▼
"title" => array:1 [▼
0 => "The title field is required."
]
"slug" => array:1 [▼
0 => "The slug field is required."
]
"content" => array:1 [▼
0 => "The content field is required."
]
"category_id" => array:1 [▼
0 => "The category id field is required."
]
"description" => array:1 [▼
0 => "The description field is required."
]
]
#format: ":message"
}
88
dd($validated->errors());
Que por supuesto, puedes pasar a tu vista para mostrarlos en la misma o desencadenar el proceso que prefieras.
O los datos:
dd($validator->valid());
Validaciones mediante una clase FormRequest
Este es el esquema que generalmente empleamos ya que, nos permite tener todas las reglas de validación en un
archivo aparte.
Para generar esta clase, tenemos que usar un comando:
$ php artisan make:request <NombreRequest>
En donde "NombreRequest" es el nombre del archivo y clase; también podemos guardarlos en una carpeta, al
igual que ocurre con los controladores; así que:
$ php artisan make:request Post/StoreRequest
Y tendremos un nuevo archivo en:
App/Http/Requests/Post/StoreRequest.php
Y dentro de este archivo, crea una clase como:
<?php
namespace App\Http\Requests\Post;
use Illuminate\Foundation\Http\FormRequest;
class StoreRequest extends FormRequest
{
/**
* Determine if the user is authorized to make this request.
*/
public function authorize(): bool
{
return true;
}
/**
* Get the validation rules that apply to the request.
*
89
* @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
*/
public function rules(): array
{
return [
//
];
}
}
El cual, tiene un método llamado rules() en el cual definimos las reglas de validación que vimos anteriormente.
Aparte, tenemos que autorizar el request; para eso, en el método authorize() cambiamos el retorno de false por
un true, con el método de authorize() se pueden colocar comprobaciones adicionales por ejemplo, cuando el
usuario autenticado tiene un rol, se puede comprobar en el mencionado método de si el usuario tienen un role
específico acepta la validación (retorna true) caso contrario, no tiene el role y no permite aplicar las validaciones.
Finalmente, queda como:
<?php
namespace App\Http\Requests\Post;
use Illuminate\Foundation\Http\FormRequest;
class StoreRequest extends FormRequest
{
/**
* Determine if the user is authorized to make this request.
*/
public function authorize(): bool
{
return true;
}
/**
* Get the validation rules that apply to the request.
*
* @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
*/
public function rules(): array
{
return [
"title" => "required|min:5|max:500",
"slug" => "required|min:5|max:500",
"content" => "required|min:7",
90
"category_id" => "required|integer",
"description" => "required|min:7",
"posted" => "required"
];
}
}
Y lo inyectamos en el método de store():
public function store(StoreRequest $request): RedirectResponse
{
Post::create($request->all());
return to_route("post.index");
}
Validar el slug
Para que el slug sea único qué es lo que usualmente queremos en este tipo de campos, definimos la regla
unique, a la cual, hay que indicarle la tabla por la que debe de buscar, en este caso, la tabla de posts:
"slug" => "required|min:5|max:500|unique:posts"
Con esto, si colocamos un slug que pertenezca a otro post, veremos un error como el siguiente:
The slug has already been taken.
Mostrar errores del formulario
Finalmente, vamos a mostrar los errores del formulario; para esto, vamos a crear un fragmento de vista para que
pueda ser fácilmente reutilizable; el nombre y ubicación depende de ti; pero en el libro recomendamos la
siguiente estructura:
dashboard/fragment/_errors-form.blade.php
@if ($errors->any())
@foreach ($errors->all() as $e)
<div class="error">
{{ $e }}
</div>
@endforeach
@endif
Automáticamente se crea una variable llamada $errors al momento de aplicar validaciones mediante el request o
empleando las clases validadoras; primero preguntamos si tenemos al menos un error mediante any() y si hay
errores en el formulario, son devueltos en un array mediante el método all() que luego son iterados y mostrados.
Para mostrar los errores del formulario, agregamos el fragmento de vista en la vista del formulario:
91
resources\views\dashboard\post\create.blade.php
***
@include('dashboard.fragment._errors-form')
<form action="{{ route('post.store') }}" method="post">
***
Y si enviamos un formulario inválido, veremos algunos errores como los siguientes:
The title field is required.
The slug must be at least 5 characters.
The content field is required.
The category id field is required.
The description field is required.
Ya con esto, logramos la operación más complicada del CRUD, que es la de creación, aprendimos a manejar los
formularios, la vista de creación y procesar los datos para crear registros en la base de datos.
Listado
Ya tenemos el proceso de crear listo, que es la parte más fuerte de un CRUD; vamos a crear ahora el método de
index() para construir un listado de nuestros POST y poder enlazar el resto de las opciones del post:
app\Http\Controllers\Dashboard\PostController.php
public function index(): View
{
$posts = Post::get();
return view('dashboard.post.index', compact('posts'));
}
Si analizamos la respuesta de vuelta:
dd($posts)
Verás que es instancia del modelo de Post:
Illuminate\Database\Eloquent\Collection {#1247
#items: array:1 [
0 => App\Models\Post {#1249
#items: array:2 [
0 => App\Models\Post
1 => App\Models\Post
Vamos a crear la vista para pintar todos los posts en una tabla:
92
dashboard/post/index.blade.php
<table>
<thead>
<tr>
<td>
Id
</td>
<td>
Title
</td>
<td>
Posted
</td>
<td>
Category
</td>
</tr>
</thead>
<tbody>
@foreach ($posts as $p)
<tr>
<td>
{{ $p->id }}
</td>
<td>
{{ $p->title }}
</td>
<td>
{{ $p->posted }}
</td>
<td>
{{ $p->category->title }}
</td>
</tr>
@endforeach
</tbody>
</table>
Listado paginado
Si queremos los registros paginados para que aparezcan de una manera organizada y bajo demanda, podemos
usar el método de paginate() en vez de la de get(); este método recibe como parámetro el nivel de paginación.
Por ejemplo, una paginación de dos:
93
$posts = Post::paginate(2);
dd($posts)
Obtenemos:
Illuminate\Pagination\LengthAwarePaginator {#1217
#items: Illuminate\Database\Eloquent\Collection {#1210
#items: array:2 [
0 => App\Models\Post
1 => App\Models\Post
]
***
}
#perPage: 2
#currentPage: 1
#path: "http://larafirststeps.test/post"
***
#total: 5
#lastPage: 3
En la cual, en los "items" nos dice que tenemos dos elementos (por la paginación de dos) y un total de 3 páginas
(lastPage).
Si empleas una paginación de cuatro:
$posts = Post::paginate(4);
dd($posts);
Obtenemos:
Illuminate\Pagination\LengthAwarePaginator {#1219 ▼
#items: Illuminate\Database\Eloquent\Collection {#1213 ▼
#items: array:4 [▼
0 => App\Models\Post
1 => App\Models\Post
2 => App\Models\Post
3 => App\Models\Post ]
***
}
#perPage: 4
#currentPage: 1
#path: "http://larafirststeps.test/post"
***
#total: 5
#lastPage: 2
}
94
En la cual, en los "items" nos dice que tenemos cuatro elementos (por la paginación de dos) y un total de 2
páginas (lastPage).
Por supuesto, para este ejemplo estamos suponiendo que tienes más de cinco posts en la base de datos; ya que
si tienes menos registros que el nivel de paginación:
Illuminate\Pagination\LengthAwarePaginator {#1206 ▼
#items: Illuminate\Database\Eloquent\Collection {#1214 ▼
#items: array:5 [▼
0 => App\Models\Post
1 => App\Models\Post
2 => App\Models\Post
3 => App\Models\Post
4 => App\Models\Post
]
***
}
#perPage: 10
#currentPage: 1
#path: "http://larafirststeps.test/post"
***
#total: 5
#lastPage: 1
}
Tendremos únicamente los cinco elementos con una paginación de un nivel.
Finalmente, el método de index() queda como:
public function index(): View
{
$posts = Post::paginate(2); // personaliza la paginacion como quieras
return view('dashboard.post.index', compact('posts'));
}
Generamos los enlaces de paginación en la vista:
resources\views\dashboard\post\index.blade.php
***
</table>
{{ $posts->links() }}
Explicación del código anterior
95
Puedes emplear cualquier estructura para pintar tus posts; pero vamos a usar el elemento HTML por excelencia
para construir listados, una tabla que vamos llenando uno a uno mediante un foreach; veremos en nuestro
navegador:
Figura 6-5: Listado paginado
Finalmente, para construir los enlaces de paginación, empleamos el método de links() a los cuales, si analizas el
HTML:
resources\views\dashboard\post\index.blade.php
<nav role="navigation" aria-label="Pagination Navigation" class="flex items-center
justify-between">
<div class="flex justify-between flex-1 sm:hidden">
96
<span class="relative inline-flex items-center px-4 py-2
text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default leading-5
rounded-md">
&laquo; Previous
</span>
<a href="http://larafirststeps.test/post?page=2" ***>
Next &raquo;
</a>
</div>
<div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
<div>
<p class="text-sm text-gray-700 leading-5">
Showing
<span class="font-medium">1</span>
to
<span class="font-medium">2</span>
of
<span class="font-medium">5</span>
results
</p>
</div>
<div>
<span class="relative z-0 inline-flex shadow-sm rounded-md">
<svg ***>
<path ***>
</svg>
</a>
</span>
</div>
</div>
</nav>
Verás que tiene iconos SVG junto con una estructura HTML bien formada; Laravel, emplea para construir este
diseño Tailwind.css que es un framework web basado en clases; y, al no estar instalado aún en nuestro proyecto,
el estilo se rompe y se visualiza de la siguiente manera:
97
Figura 6-6: Icono de paginación
Pero esto lo arreglaremos cuando trabajemos con Tailwind.css en otro capítulo.
Crear opciones CRUD
Al tener ya lista la página de listado, vamos a aprovecharla para vincular el resto de las operaciones CRUD de
nuestra aplicación, colocando los enlaces correspondientes:
resources\views\dashboard\post\index.blade.php
<a href="{{ route("post.create") }}">Create</a>
***
<table>
<thead>
***
<td>
Category
</td>
<td>
Options
</td>
***
<tbody>
@foreach ($posts as $p)
<tr>
***
<td>
98
{{ $p->posted }}
</td>
<td>
<a href="{{ route('post.edit', $p) }}">Edit</a>
<a href="{{ route('post.show', $p) }}">Show</a>
<form action="{{ route('post.destroy', $p) }}" method="post">
@method('DELETE')
@csrf
<button type="submit">Delete</button>
</form>
</td>
</tr>
@endforeach
</tbody>
</table>
***
Un caso especial es el de eliminar, que, al requerir una petición de tipo DELETE, tenemos que emplear un
formulario para poder simular la operación de tipo DELETE mediante la directiva de method y un formulario de
tipo POST; esto mismo vamos a tener que hacerlo cuando trabajemos con la operación de editar, pero
empleando un PUT o PATCH en lugar de DELETE.
Crear un layout
Antes de seguir avanzando, vamos a empezar a organizar un poco el código de nuestra aplicación; vamos a
crear un layout o vista maestra como hicimos anteriormente en el capítulo de las vistas; creamos un layout para
el dashboard en:
resources\views\dashboard\layout.blade.php
Con la siguiente estructura:
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="ie=edge">
<title>Dashboard</title>
</head>
<body>
@yield('content')
</body>
</html>
99
Luego, en la vista de create.blade.php, lo adaptamos:
@extends('dashboard.layout')
@section('content')
@include('dashboard.fragment._errors-form')
<form action="{{ route('post.store') }}" method="post">
***
</form>
@endsection
Y también, en la vista de listado:
@extends('dashboard.layout')
@section('content')
<a href="{{ route("post.create") }}">Crear</a>
<table>
***
</table>
{{ $posts->links() }}
@endsection
Editar
En este punto, ya sabemos cómo crear registros y validar los datos y mostrarlos en la vista; vamos a reutilizar
todo este esquema para poder crear la opción de editar:
Crearemos una vista en:
resources\views\dashboard\post\edit.blade.php
Con el siguiente contenido:
@extends('dashboard.master')
@section('content')
@include('dashboard.fragment._errors-form')
100
<form action="{{ route('post.update', $post->id) }}" method="post">
@method('PATCH')
@csrf
<label for="">Title</label>
<input type="text" name="title" value="{{ $post->title }}">
<label for="">Slug</label>
<input type="text" name="slug" value="{{ $post->slug }}">
<label for="">Content</label>
<textarea name="content">{{ $post->content }}</textarea>
<label for="">Category</label>
<select name="category_id">
@foreach ($categories as $title => $id)
<option {{ $post->category->id == $id ? 'selected' : ''}} value="{{ $id
}}">{{ $title }}</option>
@endforeach
</select>
<label for="">Description</label>
<textarea name="description">{{ $post->description }}</textarea>
<label for="">Posted</label>
<select name="posted">
<option {{ $post->posted == 'not' ? 'selected' : ''}} value="not">Not</option>
<option {{ $post->posted == 'yes' ? 'selected' : ''}} value="yes">Yes</option>
</select>
<button type="submit">Send</button>
</form>
@endsection
Verás que es exactamente igual a las de crear salvo por tres cambios:
1. Indicamos el valor por defecto mediante el post seleccionado por nuestro usuario desde el listado;
colocamos esta data en cada uno de los campos de formulario.
2. Empleamos una directiva llamada method; esta directiva la tenemos que emplear, ya que recuerda que
por el API de HTML solamente podemos enviar peticiones de tipo GET y POST, pero, en el caso de la
ruta de tipo recurso que creamos anteriormente, necesitamos una ruta de tipo PUT o PATCH, así que,
para poder simular una petición de tipo PUT, PATCH o DELETE, tenemos que usar un formulario, definido
de tipo POST y emplear la directiva con el método que queramos emplear; en este caso, de tipo PATCH.
3. El action del formulario, indicando la ruta correspondiente para procesar el formulario.
101
Para el método de editar:
public function edit(Post $post): View
{
$categories = Category::pluck('id', 'title');
return view('dashboard.post.edit', compact('categories', 'post'));
}
Vamos a presentar una clase de validación para aplicar las reglas que vimos anteriormente; pero, ahora sobre la
opción de edición.
Podemos crear archivos de validación con artisan; para eso:
$ php artisan make:request Post/PutRequest
En dónde make:request indica el recurso que vamos a crear, en este caso, un archivo para manejar las
validaciones y Post/PutRequest, es el nombre y ubicación del archivo; la ubicación es completamente opcional y
puedes prescindir de la carpeta y llamarla como PostPutRequest, pero, resulta bastante útil tener estas clases
organizadas, ya que usualmente necesitamos más de una por recurso, como es nuestro caso.
Y tendremos:
<?php
namespace App\Http\Requests\Post;
use Illuminate\Foundation\Http\FormRequest;
class PutRequest extends FormRequest
{
/**
* Determine if the user is authorized to make this request.
*/
public function authorize(): bool
{
return false;
}
/**
* Get the validation rules that apply to the request.
*
* @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
*/
public function rules(): array
{
return [
//
102
];
}
}
En donde " *** reglas de validación " indicamos las reglas de validación.
Recuerda autorizar la petición colocando true en el método de authorize() y vamos a definir las siguientes reglas:
<?php
namespace App\Http\Requests\Post;
use Illuminate\Foundation\Http\FormRequest;
class PutRequest extends FormRequest
{
/**
* Determine if the user is authorized to make this request.
*/
public function authorize(): bool
{
return true;
}
/**
* Get the validation rules that apply to the request.
*
* @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
*/
public function rules(): array
{
return [
"title" => "required|min:5|max:500",
// "slug" => "required|min:5|max:500|unique:posts"
"content" => "required|min:7",
"category_id" => "required|integer",
"description" => "required|min:7",
"posted" => "required"
];
}
}
Y ahora, para poder emplear esta clase de validación desde un controlador, se lo podemos inyectar directamente
en el método de update():
use App\Http\Requests\Post\PutRequest;
103
***
public function update(PutRequest $request, Post $post): RedirectResponse
{
$post->update($request->validated());
return to_route("post.index");
}
De tal manera que, su comportamiento es exactamente igual a cuando validamos en el método de create(); pero
haciendo pequeñas adaptaciones.
Validar el slug
Otro detalle importante es que, la validación para el slug está comentada; esto lo puedes manejar de varias
maneras; ya que el slug es un identificador que se actualiza en raras ocasiones, pudieras considerar que el
mismo no podrá ser cambiado una vez creado el mismo.
Si quieres que sea actualizable, la regla que tiene actualmente:
"slug" => "required|min:5|max:500|unique:posts"
Ocasionará un conflicto en la actualización, ya que, va a chocar contra sí misma y no podrás actualizar el post al
menos que actualices el slug cada vez que quieras actualizar el post en cuestión.
Para evitar este comportamiento, podemos modificarlo de la siguiente manera:
"slug" => "required|min:5|max:500|unique:posts,slug,".$this->route("post")->id,
En dónde $this->route("post") es el parámetro de nuestra ruta, en este caso el post:
PUT|PATCH post/{post}
Puedes colocar un dd() en el método de rules(), para que veas algo como lo siguiente al intentar actualizar un
formulario:
^ App\Models\Post {#1215 ▼
***
#attributes: array:10 [▼
"id" => 6
"title" => "Other title"
***
"category_id" => 2
]
El modelo inyectado del post.
104
Finalmente, la regla anterior, lo que hace es, indicar una excepción en el comportamiento que tiene por defecto el
unique; deshabilitando la aplicación de la regla según el identificador del post ($this->route("post")->id) sobre
el slug.
Fragmento de vista para los campos
Ya que vimos que los formularios de create.blade.php y edit.blade.php son prácticamente idénticos; vamos a
emplear un fragmento de vista en donde vamos a definir estos campos:
resources\views\dashboard\post\_form.blade.php
Y los reutilizamos en cada una de las vistas del formulario:
@csrf
<label for="">Title</label>
<input type="text" name="title" value="{{ $post->title }}">
<label for="">Slug</label>
<input type="text" name="slug" value="{{ $post->slug }}">
<label for="">Category</label>
<select name="category_id">
<option value=""></option>
@foreach ($categories as $title => $id)
<option {{ "$post->category_id" == $id ? 'selected' : '' }} value="{{ $id }}">
{{ $title }}</option>
@endforeach
</select>
<label for="">Posted</label>
<select name="posted">
<option {{ $post->posted == 'not' ? 'selected' : '' }} value="not">No</option>
<option {{ $post->posted == 'yes' ? 'selected' : '' }} value="yes">Si</option>
</select>
<label for="">Content</label>
<textarea name="content"> {{ $post->content }}</textarea>
<label for="">Description</label>
<textarea name="description">{{ $post->description }}</textarea>
<button type="submit">Send</button>
En la vista de create.blade.php:
105
@extends('dashboard.layout')
@section('content')
<h1>Create Post</h1>
@include('dashboard.fragment._errors-form')
<form action="{{ route('post.store') }}" method="post">
@include('dashboard.post._form')
</form>
@endsection
En la vista de edit.blade.php:
@extends('dashboard.layout')
@section('content')
<h1>Update Post: {{ $post->title }}</h1>
@include('dashboard.fragment._errors-form')
<form action="{{ route('post.update',$post->id) }}" method="post"
enctype="multipart/form-data">
@method("PATCH")
@include('dashboard.post._form',["task" => "edit"])
</form>
@endsection
Ahora, como puedes ver, tenemos el problema de que en editar le pasamos el post y en crear no; para arreglar
esto fácilmente, vamos a cambiar un poco la estructura del método de create():
public function create(): View
{
$categories = Category::pluck('id', 'title');
$post = new Post();
return view('dashboard.post.create', compact('categories', 'post'));
}
En este punto, prueba el proceso de crear y editar y debería de funcionar correctamente.
Valores anteriores
Un problema que tenemos actualmente, es que, cuando intentamos editar o crear un registro, si colocamos datos
inválidos, pierde automáticamente los valores que coloca nuestro usuario; por ejemplo, si intentamos modificar el
siguiente registro:
106
Figura 6-7: Formulario válido
Y colocamos datos incorrectos:
Figura 6-8: Formulario inválido
107
Verás que esa referencia se pierde al momento de hacer la redirección y mostrar los errores:
Figura 6-9: Errores de formulario
Para corregir esto, vamos a hacer uno de la función de old() la cual permite recordar el valor anterior y evitar el
comportamiento señalado anteriormente.
La función old() recibe dos parámetros:
1. El nombre del campo, y esto es, para que Laravel pueda verificar si existe en el request o petición y si
existe, establece el valor en el formulario.
2. Valor por defecto, si no existe en el request un valor que mostrar para el campo especificado, la función
de old() usa este valor en su lugar.
@csrf
<label for="">Título</label>
<input type="text" name="title" value="{{ old("title",$post->title) }}">
<label for="">Slug</label>
<input type="text" name="slug" value="{{ old("slug",$post->slug) }}">
<label for="">Categoría</label>
<select name="category_id">
108
<option value=""></option>
@foreach ($categories as $title => $id)
<option {{ old("category_id","$post->category_id") == $id ? "selected" : "" }}
value="{{ $id }}">{{ $title }}</option>
@endforeach
</select>
<label for="">Posteado</label>
<select name="posted">
<option {{ old("posted",$post->posted) == "not" ? "selected" : "" }}
value="not">No</option>
<option {{ old("posted",$post->posted) == "yes" ? "selected" : "" }}
value="yes">Si</option>
</select>
<label for="">Contenido</label>
<textarea name="content"> {{ old("content",$post->content) }}</textarea>
<label for="">Descripción</label>
<textarea name="description">{{ old("description",$post->description) }}</textarea>
<button type="submit">Enviar</button>
Si hacemos la misma prueba anterior, verás que ahora sí recuerda el valor anterior.
Carga de imágenes/archivos
La carga de archivos, en este caso, de imágenes, es una tarea fundamental en cualquier sistema, en este caso,
nos interesa registrar una única referencia en el campo image del post, pero, puedes usar esta lógica si tienes
otro tipo de relación.
La carga de imagen, es una característica que solamente vamos a usar en el formulario de edición y no en el de
crear; esto no tiene que ser así, perfectamente puedes adaptarla en la fase de creación, pero, por cuestiones de
hacer más sencillo en código que vamos a explicar y un ejercicio un poco más interesante, lo vamos a hacer de
esta forma.
Aparte de que es importante notar que, existen dos procesos cuando hay una carga de imagen de por medio en
un formulario de edición:
1. El flujo normal de la actualización del registro.
2. La carga de la imagen.
De las cuales, el id del registro es fundamental para referenciar la imagen cargada, y para generar este id en la
fase de creación, todas las validaciones tuvieron que ser superadas y el registro creado.
FInalmente, al ser un campo que solamente vamos a usar en edición, no queremos que aparezca en la de
creación; vamos a emplear una variable, que puede tener cualquier nombre o definición, pero es necesaria para
que desde la vista de _form.blade.php saber si estamos en editar o crear:
109
En la vista de edit.blade.php:
@extends('dashboard.layout')
***
<form action="{{ route('post.update', $post->id) }}" method="post"
enctype='multipart/form-data'>
***
@include('dashboard.post._form',["task" => "edit"])
</form>
@endsection
También le pasamos el atributo enctype='multipart/form-data' de HTML para indicar la carga de archivos.
En la vista de _form.blade.php:
***
<textarea name="description">{{ old('description', $post->description) }}</textarea>
@if (isset($task) && $task == 'edit')
<label for="">Imagen</label>
<input type="file" name="image">
@endif
<button type="submit">Enviar</button>
***
Así que, solamente pintaremos el campo de carga de imagen, si le pasamos la variable de task y la misma tiene
el valor de “edit”.
Importante notar que, ahora sí vamos a colocar una validación; lo típico, indicar mediante el mime el tipo de
archivo soportado y el tamaño máximo expresado en kbs:
public function rules(): array
{
dd($this->route("post"));
return [
***
"image" => "mimes:jpeg,jpg,png|max:10240"
];
}
Si quieres conocer que más validaciones puedes emplear:
https://laravel.com/docs/master/validation#rule-image
110
En la función de update(), finalmente, vamos a presentar el proceso para la carga de una imagen:
public function update(PutRequest $request, Post $post)
{
$data = $request->validated();
if( isset($data["image"])){
$data["image"] = $filename = time().".".$data["image"]->extension();
$request->image->move(public_path("image"), $filename);
}
$post->update($data);
}
Explicación del código anterior
Vamos a ver en varios puntos los aspectos fundamentales:
1. Al ser la imagen un campo opcional, preguntamos con un condicional si el campo está o no presente; si
no está presente, significa que el usuario no seleccionó una imagen y, por lo tanto, no hay ningún proceso
que realizar.
2. Primero, generamos un nombre para la imagen de manera aleatoria; en este caso, usamos la función de
time() para generar un nombre aleatorio en base a la hora actual y le concateno la extensión de la
imagen.
a. Pero existen variantes, si quieres emplear el nombre original de la imagen:
$request->image->getClientOriginalName()
b. Si quieres el nombre via hash $request->image->hashName()
3. Finalmente, movemos la imagen; en Laravel, las carpetas son discos; en Laravel tenemos muchos discos
que podemos y configurar; si vamos al archivo de: config/filesystems.php verás una estructura como a
siguiente:
'disks' => [
'local' => [
'driver' => 'local',
'root' => storage_path('app'),
],
'public' => [
'driver' => 'local',
***
],
's3' => [
'driver' => 's3',
***
],
111
],
La cual define los discos que podemos emplear; fíjate que, inclusive hay referencias al servicio de Amazon Web
Server; por lo tanto, un disco, es cualquier sistema que nos permita almacenar archivos; el más sencillo sería el
de la carpeta public que estamos empleando, pero existen más.
La función de public_path(), recibe como argumento la ubicación de donde quieres guardar la imagen; así que,
con esto, puedes indicar que la quieres guardar en otra ubicación; por ejemplo:
public_path("image")
Si quieres obtener más información sobre las operaciones que puedes realizar con la imagen:
https://laravel.com/api/master/Illuminate/Http/UploadedFile.html
Finalmente, al intentar cargar una imagen con el formulario:
Figura 6-10: Imagen cargada
Eliminar
Para eliminar, ya al tener el formulario definido en el listado, lo único que tenemos que hacer es implementarlo;
ya tenemos la referencia al post, y para eliminar un registro empleando Eloquent tenemos el uso del método
delete():
public function destroy(Post $post): RedirectResponse
{
$post->delete();
return to_route("post.index");
}
Vista de detalle
El proceso de detalles es igual de simple, y lo único que hacemos es pasar la referencia al post, desde el método
de show():
112
El método de show():
public function show(Post $post): View
{
return view("dashboard.post.show",compact('post'));
}
Y su vista:
resources\views\dashboard\post\show.blade.php
@extends('dashboard.master')
@section('content')
<h1>{{ $post->title }}</h1>
<span>{{ $post->posted }}</span>
<span>{{ $post->category->title }}</span>
<div>
{{ $post->description }}
</div>
<div>
{{ $post->content }}
</div>
<img src="/uploads/posts/{{ $post->image }}" style="width:250px" alt="{{ $post->title
}}">
{{ $post->image }}
@endsection
CRUD de categorías
El CRUD para las categorías es una variante de la que vimos para el post, pero más sencilla, al solo tener el
campo de title y slug, lo único que tenemos que hacer es copiar y pegar el desarrollo que hicimos para el post,
hacer los renombres y quitar las referencias que no necesitamos, como la búsqueda de todas las categorías y los
campos que no vamos a usar.
Fíjate en los namespaces de cada clase para determinar dónde guardas los archivos, ya que, todos están en
subcarpetas.
Las clases de validación:
113
app\Http\Requests\Category\PutRequest.php
<?php
namespace App\Http\Requests\Category;
use Illuminate\Foundation\Http\FormRequest;
class PutRequest extends FormRequest
{
public function authorize(): bool
{
return true;
}
public function rules(): array
{
return [
"title" => "required|min:5|max:500",
"slug" =>
"required|min:5|max:500|unique:categories,slug,".$this->route("category")->id,
];
}
}
app\Http\Requests\Category\StoreRequest.php
<?php
namespace App\Http\Requests\Category;
use Illuminate\Foundation\Http\FormRequest;
class StoreRequest extends FormRequest
{
protected function prepareForValidation()
{
$this->merge([
'slug' => str($this->title)->slug()
]);
}
114
static public function myRules()
{
return [
"title" => "required|min:5|max:500",
"slug" => "required|min:5|max:500|unique:posts",
];
}
public function authorize(): bool
{
return true;
}
public function rules(): array
{
return $this->myRules();
}
}
El controlador:
app\Http\Controllers\Dashboard\CategoryController.php
<?php
namespace App\Http\Controllers\Dashboard;
use App\Models\Category;
use App\Http\Controllers\Controller;
use App\Http\Requests\Category\PutRequest;
use App\Http\Requests\Category\StoreRequest;
class CategoryController extends Controller
{
public function index()
{
$categories = Category::paginate(2);
return view('dashboard/category/index', compact('categories'));
}
public function create()
{
$category = new Category();
return view('dashboard.category.create', compact('category'));
115
}
public function store(StoreRequest $request)
{
Category::create($request->validated());
return to_route('category.index');
}
public function show(Category $category)
{
return view('dashboard/category/show',['category'=> $category]);
}
public function edit(Category $category)
{
return view('dashboard.category.edit', compact('category'));
}
public function update(PutRequest $request, Category $category)
{
$category->update($request->validated());
return to_route('category.index');
}
public function destroy(Category $category)
{
$category->delete();
return to_route('category.index');
}
}
Las vistas van a tener la siguiente organización:
116
Figura 6-11: Vistas para las categorías
La vista de campos de formulario:
resources\views\dashboard\category\_form.blade.php
@csrf
<label for="">Title</label>
<input type="text" name="title" value="{{ old('title', $category->title) }}">
<label for="">Slug</label>
<input type="text" name="slug" value="{{ old('slug', $category->slug) }}">
<button type="submit">Send</button>
La vista de crear:
resources\views\dashboard\category\create.blade.php
117
@extends('dashboard.layout')
@section('content')
@include('dashboard.fragment._errors-form')
<form action="{{ route('category.store') }}" method="post">
@include('dashboard.category._form')
</form>
@endsection
La vista de editar:
resources\views\dashboard\category\edit.blade.php
@extends('dashboard.layout')
@section('content')
@include('dashboard.fragment._errors-form')
<form action="{{ route('category.update',$category->id) }}" method="post">
@method("PATCH")
@include('dashboard.category._form')
</form>
@endsection
La vista de index:
resources\views\dashboard\category\index.blade.php
@extends('dashboard.master')
@section('content')
<a href="{{ route('category.create') }}" target="blank">Create</a>
<table>
<thead>
<tr>
<td>
Id
118
</td>
<td>
Title
</td>
<td>
Options
</td>
</tr>
</thead>
<tbody>
@foreach ($categories as $c)
<tr>
<td>
{{ $c->id }}
</td>
<td>
{{ $c->title }}
</td>
<td>
<a href="{{ route('category.show',$c) }}">Show</a>
<a href="{{ route('category.edit',$c) }}">Edit</a>
<form action="{{ route('category.destroy', $c) }}" method="post">
@method('DELETE')
@csrf
<button type="submit">Delete</button>
</form>
</td>
</tr>
@endforeach
</tbody>
</table>
{{ $categories->links() }}
@endsection
La vista de detalle:
resources\views\dashboard\category\show.blade.php
@extends('dashboard.layout')
@section('content')
<h1>{{ $category->title }}</h1>
@endsection
119
Y su ruta:
routes\web.php
Route::resource('category', CategoryController::class);
Tinker, la consola interactiva de Laravel
Tinker no es más que una consola interactiva, al igual que ocurre en Python que escribimos en la terminal
consola 'python' y se habilita la terminal para que podamos ejecutar comandos de Python, en Laravel tenemos su
equivalente en el cual, podemos importar y emplear los distintos módulos de Python para hacer pruebas, es
decir, es particularmente útil cuando no sabemos cómo funciona un módulo de Laravel y queremos hacer debug
del mismo.
Muchos frameworks como Django o Flask incorporan este tipo de herramientas los cuales son ideales como se
comentó antes, para hacer pruebas, ejecutar código rápidamente, examinar datos, identificar errores, entre otros.
Para ejecutarlo, abrimos la terminal y ejecutamos:
$ php artisan tinker
Desde aquí, podemos cargar modelos, librerías o cualquier otro módulo que esté instalado o forme parte de
Laravel, por ejemplo:
use App\Models\Category;
$category = new Category();
$category->title = 'Cate 1';
$category->slug = 'cate-1';
$category->save();
Category::all();
Rutas agrupadas
También podemos agrupar nuestras rutas en un solo conjunto o grupo e indicar características, comunes, lo cual
es particularmente útil cuando desarrollamos un módulo que constan de varios controladores, en nuestro
ejemplo, queremos colocar el prefijo de dashboard en las URLs para el módulo de gestión:
routes\web.php
Route::group(['prefix' => 'dashboard'], function () {
Route::resource('post', PostController::class);
Route::resource('category', CategoryController::class);
});
120
Entonces por ejemplo, en vez de colocar:
http://larafirststeps.test/post
Para acceder a cualquier controlador de nuestra aplicación, debemos de colocar:
http://larafirststeps.test/dashboard/post
Código fuente del capítulo:
https://github.com/libredesarrollo/book-course-laravel-base-11/releases/tag/v0.3
121
Capítulo 7: Mensajes por sesión y flash
En este capítulo, vamos a presentar el uso de los mensajes tipo flash, que solamente duran un request y son
ideales para mostrar mensajes informativos sobre la última operación realizada y los mensajes por sesión.
Mensajes tipo flash
Los mensajes de tipo flash, son aquellos que solamente duran un request; a diferencia, de la sesión que
tenemos en el servidor y administramos por PHP a la cual podemos configurar para que dure, 30 minutos, horas,
días… o hasta que el usuario destruya la sección; el tiempo de vida de la sesión flash es de apenas una sola
petición o request; para emplearla tenemos:
$request->session()->flash('status',"Registro actualizado.");
En donde el primer parámetro es la key y el segundo el valor; con esta key, podemos consumir el valor de la
siguiente manera:
session('status')
Otra variante más sencilla si estamos empleando una redirección, como es nuestro caso para las operaciones de
eliminar, crear y editar, es inyectarla directamente en la redirección:
return to_route("post.index")->with('status',"Registro creado.");
Caso práctico
En nuestros métodos controladores para eliminar, crear y actualizar, vamos a crear establecer un mensaje tipo
flash para mostrar a nuestro usuario al momento de realizar estas operaciones:
app\Http\Controllers\Dashboard\PostController.php
<?php
***
class PostController extends Controller
{
***
public function store(StoreRequest $request)
{
Post::create($request->validated());
return to_route('post.index')->with('status', 'Post created');
}
public function update(PutRequest $request, Post $post)
122
{
***
$post->update($data);
return to_route('post.index')->with('status', 'Post updated');
}
public function destroy(Post $post)
{
$post->delete();
return to_route('post.index')->with('status', 'Post delete');
}
}
Para el controlador de categorías, puedes aplicar el mismo cambio que el mostrado en el controlador de post.
Finalmente, agregamos en nuestro layout:
resources\views\dashboard\master.blade.php
***
<body>
@if (session('status'))
{{ session('status') }}
@endif
@yield('content')
***
Y al realizar alguna operación CRUD:
Figura 7-1: Mensaje tipo flash
Tenemos un equivalente al código anterior usando la directiva se session:
@session('status')
{{ $value }}
@endsession --}}
123
Sesión
La sesión es el sistema por excelencia para almacenar datos de usuario de manera no persistente, es decir, es
un almacenamiento de datos volátil como en el caso de los mensajes tipo flash, aunque su duración depende de
las configuraciones realizadas en el proyecto en Laravel y el servidor; más adelante que presentemos el uso de
la autenticación, todos los datos del usuario se almacenan en la sesión en formato clave/valor como en el caso
anterior.
Para gestionar la sesión, como todo en Laravel, tenemos varias formas, veamos algunos; para obtener un valor
de la sesión:
$value = session('key');
Para obtener un valor de la sesión, pero si no está definido devolver un valor preestablecido por defecto:
$value = session('key', 'default');
Para almacenar un valor en la sesión:
session(['key' => 'value']);
Destruir toda la sesión:
session()->flush();
O por la key:
session()->forget('key');
Desde blade, podemos acceder a la sesión del a misma forma que hacemos con los mensajes tipo flash:
@session('key')
{{ $value }}
@endsession --}}
Código fuente del capítulo:
https://github.com/libredesarrollo/book-course-laravel-base-11/releases/tag/v0.4
124
Capítulo 8: Rutas
En este capítulo, vamos a ver un poco más a fondo el uso de las rutas en Laravel; que, aunque las hemos usado
hasta este capítulo en la creación de nuestro CRUD, aún tenemos opciones que tenemos que conocer para
poder aprovecharlas de manera efectiva.
Nombre en las rutas
Para definir un nombre a una ruta, usamos el método de name(), que recibe como parámetro, el nombre de la
ruta; por ejemplo:
Route::get('post','index')->name("post.index");
Parámetros
El uso de los parámetros es fundamental en las URLs, que podamos pasar identificadores y demás parámetros
de control son necesarios para el funcionamiento de nuestra aplicación, ya que, todos estos datos de control,
provienen de la selección de nuestro usuario (como en el listado de post o categorías, cuando damos click en el
detalle de dichos recursos, pasamos el id) y son procesados por el controlador (o componentes similares).
Tenemos dos tipos para esto, parámetros opcionales y obligatorios.
Parámetros obligatorios
Los parámetros obligatorios son los que hemos tratado hasta este punto; en el cual, si indicamos un parámetro
en la URL, debes de pasarlo por la URL:
Route::get('/test/{id}', function (int $id) {
echo $id;
});
Con esto, si vamos a la página de:
O si no le pasamos el parámetro:
125
Parámetros opcionales
Los parámetros opcionales, son aquellos que pueden estar o no presenten, y como son opcionales, tenemos que
dar un valor por defecto a dichos parámetros:
Route::get('/test/{id?}', function (int $id = 10) {
echo $id;
});
En el ejemplo anterior, tenemos el id, que es opcional, y por ende, si el usuario no le pasa, le establecemos un 10
(por ejemplo):
Con esto, si vamos a la página de:
O si no le pasamos el parámetro:
126
Rutas agrupadas
Esta es la característica más avanzada de las rutas y la que mayor provecho le podemos sacar; las rutas
agrupadas no es más que la clasificación de una o más rutas bajo algún criterio, que puede ser para definir un
prefijo, controlador, middleware, etc.
Todas estas rutas agrupadas tienen el siguiente formato:
Route::<opcion>(<parametros>)->group(function () {
// *** RUTAS
});
En la cual, colocamos la opción dependiendo de lo que queramos agrupar.
Middlewares
Los middlewares son aquellos componentes que se ejecutan antes del controlador (o componentes similares):
Es decir, es una especie de filtro por el cual pasan las solicitudes que nosotros queremos clasificar; para hacer
una prueba de esto, tenemos que crear un middleware.
Crearemos un middleware con:
$ php artisan make:middleware TestMiddleware
Si estableces el middleware de la siguiente manera (fíjate en el namespace para saber dónde están registrados
los mismos):
<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
127
use Symfony\Component\HttpFoundation\Response;
class TestMiddleware
{
public function handle(Request $request, Closure $next): Response
{
// return redirect("/");
return $next($request);
}
}
Creamos la ruta agrupada:
Route::middleware([App\Http\Middleware\TestMiddleware::class])->group(function () {
Route::get('/test/{id}', function (int $id) {
echo $id;
});
});
Importante notar que, podemos pasar más de un middleware, y de aquí los corchetes; finalmente probamos:
Seguirá funcionando correctamente; o, si lo defines de la siguiente manera:
<?php
***
use Illuminate\Http\RedirectResponse;
class TestMiddleware
{
public function handle(Request $request, Closure $next): RedirectResponse
{
return redirect("/");
//return $next($request);
}
128
}
Redireccionará a la página de index, que fue la que definimos en el middleware.
Con los middlewares, puedes evaluar cualquier tipo de lógica, y según las reglas que impongas en el mismo,
pueden suceder una de dos cosas:
1. Permitir el acceso al controlador al cual se accede por defecto: return $next($request)
2. Lo mandas a otra página (return redirect("/")), ya sea porque el usuario no tiene los permisos
suficientes, falta de credenciales, o similares.
Controladores
Otro tipo de agrupación que es empleado cuando tenemos muchos métodos controladores que tienen como
padre el mismo controlador:
Route::controller(PostController::class)->group(function () {
Route::get('post','index')->name("post.index");
Route::get('post/{post}','show')->name("post.show");
Route::get('post/create','create')->name("post.create");
Route::get('post/{post}/edit','edit')->name("post.edit");
Route::post('post','store')->name("post.store");
Route::put('post/{post}','update')->name("post.update");
Route::destroy('post/{post}','delete')->name("post.destroy");
});
Su equivalente sin la agrupación de rutas:
Route::get('post', [PostController::class, 'index'])->name("post.index");
Route::get('post/{post}', [PostController::class, 'show'])->name("post.show");
Route::get('post/create', [PostController::class, 'create'])->name("post.create");
Route::get('post/{post}/edit', [PostController::class, 'edit'])->name("post.edit");
Route::post('post', [PostController::class, 'store'])->name("post.store");
Route::put('post/{post}', [PostController::class, 'update'])->name("post.update");
Route::destroy('post/{post}', [PostController::class, 'delete'])->name("post.destroy");
129
Agrupadas
Esta es otra clasificación muy interesante cuya estructura varía ligeramente a la presentada anteriormente; en
esta oportunidad, como opción, colocamos el método de group().
Con este método podemos indicar el middleware (como el caso anterior):
Route::group(['middleware' => 'auth'], function () {
// *** Rutas
});
Y/o el namespace:
Route::group(['namespace' => 'Dashboard'], function() {
// Rutas de los controladores dentro del Namespace "App\Http\Controllers\Dashboard"
// *** Rutas
});
Y/o el prefijo:
Route::group(['prefix' => 'dashboard'], function () {
Route::resource('post', PostController::class);
Route::resource('category', CategoryController::class);
});
Entre otros.
Aplica en tu proyecto esta última, y ahora, para entrar en los CRUDs:
130
Y estas por indicar algunas opciones comunes.
Rutas de tipo recurso
Tenemos algunas variantes; por ejemplo, si tenemos más de una, como es nuestro caso, las podemos agrupar
como:
Route::resources([
'post' => PostController::class,
'category' => CategoryController::class,
]);
En vez de:
Route::resource('post', PostController::class);
Route::resource('category', CategoryController::class);
Muy posiblemente, cuando creas una ruta de tipo recurso, no quieres usar todos y cada uno de sus métodos,
para esto, tenemos dos opciones.
Indicar que rutas queremos excluir:
131
Route::resource('post', PostController::class)->except(['show']);
Si analizamos las rutas, verás que ya la de show no está presente:
$ php artisan r:l
GET|HEAD / ...................................
GET|HEAD dashboard/post post.index › Dashboar…
POST dashboard/post post.store › Dashboar…
GET|HEAD dashboard/post/create post.create › …
PUT|PATCH dashboard/post/{post} post.update › …
DELETE dashboard/post/{post} post.destroy
O cuáles rutas queremos conservar (la operación inversa de la mostrada anteriormente):
Route::resource('post', PostController::class)->only(['show']);
Si analizamos las rutas, veras que solamente está la de show:
$ php artisan r:l
GET|HEAD / ........................................
GET|HEAD dashboard/post/{post} post.show › Dashboa…
Al recibir un array, puedes pasar más de una ruta.
132
Capítulo 9: Laravel Breeze
Laravel Breeze lo definen en la documentación oficial como:
"Laravel Breeze es una implementación mínima y simple de todas las funciones de autenticación de Laravel,
incluido el inicio de sesión, el registro, el restablecimiento de contraseña, la verificación de correo electrónico y la
confirmación de contraseña. La capa de vista predeterminada de Laravel Breeze se compone de vistas Blade
simples diseñadas con Tailwind CSS."
IMPORTANTE: A partir de la versión 12 de Laravel ya no es recomendable emplear este paquete y puedes
apreciar que ya fue excluida de la página oficial, en su lugar, deberíamos de emplear las opciones como Livewire,
Vue o React que son las opciones que tenemos al intentar crear un nuevo proyecto en Laravel:
https://github.com/laravel/breeze
Sin embargo, el paquete todavía recibirá soporte para futuras versiones de Laravel y al no tener ninguna otra
opción que no incluya tecnologías externas como Vue o React si no queremos aprovechar los que nos provee
internamente Laravel y no emplear capas que aunque son excelentes como las mencionadas antes o incluso
Livewire, para este escrito, en el cual queremos enseñar las bases de Laravel, seguiremos empleando Laravel
Breeze por su sencillez y poca intrusión en nuestro código.
Y en pocas palabras nos ofrece dos configuraciones a nivel del proyecto:
1. Instalar y configurar Tailwind.css y Alpine.js.
2. Instalar y configurar un sencillo esquema de autenticación, registrarse, recuperar la contraseña y
middleware para el control de acceso.
Importante notar que, no es el objetivo del libro aprender Tailwind.css o Alpine.js, por lo tanto, se da por hecho
que el lector tiene ciertos conocimientos sobre estos; se intentará tener el uso a estas tecnologías de manera
sencilla, pero, si te pierdes perdido en algún punto, como recomendación general, puedes pausar en capítulo y
estudiar un poco estas tecnologías; en mi canal en Youtube encontrarás introducciones a estas tecnologías.
La página del paquete que vamos a instalar está en:
https://laravel.com/docs/master/starter-kits
Para poder instalarlo:
$ composer require laravel/breeze --dev
Y ejecutamos
$ php artisan breeze:install
Preguntará cual stack queremos emplear; si tienes experiencia, puede seleccionar cualquiera de las tecnologías
como Vue o react:
133
Which Breeze stack would you like to install?
Blade with Alpine ........................................ blade
Livewire (Volt Class API) with Alpine ........................... livewire
Livewire (Volt Functional API) with Alpine ....................................
livewire-functional
React with Inertia ......................................... react
Vue with Inertia ................................................. vue
API only .................................................. api
Pero, para iniciar de a poco, se recomienda que emplees la opción de "blade":
$ blade
Puedes habilitar el modo oscuro:
Would you like to install dark mode support?
(yes/no) [no]
Indicando "yes":
$ yes
Entre otras opciones, como las pruebas unitarias, que no serían necesarias:
Would you prefer Pest tests instead of PHPUnit?
(yes/no) [no]
Indicando "no":
$ no
Y esperas hasta que termine el proceso:
INFO Installing and building Node dependencies.
Si ves algún error como el siguiente:
failed to load config from C:\laragon\www\testlara11\vite.config.js
error when starting dev server:
Error: Cannot find module 'node:path'
Require stack:
- C:\laragon\www\testlara11\node_modules\vite\dist\node-cjs\publicUtils.cjs
- C:\laragon\www\testlara11\node_modules\vite\index.cjs
- C:\laragon\www\testlara11\vite.config.js
Significa que tienes una versión de Node muy antigua; puedes conocer tu versión de Node con:
134
$ node -v
Si vez una versión similar o inferior a esta:
v14.16.1
En el libro, estamos usando:
$ node -v
v18.8.0
Debes de actualizar tu versión; luego puedes ejecutar:
$ npm run dev
En el caso de estar empleando Laravel Sail y no tener instalado Node en tu equipo:
$ ./vendor/bin/sail npm run dev
Finalmente, veremos que es sobrescrito nuestro esquema de rutas en:
Route::get('/dashboard', function () {
return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');
Y recuerda colocar nuevamente las rutas sobrescritas:
use App\Http\Controllers\Dashboard\CategoryController;
use App\Http\Controllers\Dashboard\PostController;
***
Route::group(['prefix' => 'dashboard'], function () {
Route::resources([
'post' => PostController::class,
'category' => CategoryController::class,
]);
});
Si revisamos los archivos generados en la carpeta de resources, veremos:
resources\css\app.css
@import "tailwindcss";
resources\js\app.js
import './bootstrap';
135
import Alpine from 'alpinejs';
window.Alpine = Alpine;
Alpine.start();
Breeze configuró Tailwind.css y Alpine.js en nuestro proyecto con las importaciones correspondientes para tal fin.
Finalmente, el comando de npm run dev, genera los archivos de salida y levanta un proceso para el Hot Reload
Replacement que permite sincronizar los cambios que hagamos en el código fuente con lo que estamos viendo
en el navegador.
Sistema de autenticación
Aparte de la configuración de Tailwind y Alpine, Laravel Breeze configura un sencillo esquema de autenticación
que vamos a emplear a continuación.
Si vamos a:
http://larafirststeps.test/login
Veremos la siguiente pantalla:
Figura 9-1: Página de login en light mode
136
Una página de login, pero, necesitamos crear un usuario:
http://larafirststeps.test/register
Creamos alguno:
Figura 9-2: Página de registro en light mode
Y si vamos a la base de datos:
Figura 9-3: Crear un usuario
137
Por defecto ya iniciamos login; y como puedes ver, ya tenemos listo un bonito layout para nuestra aplicación; en
el proyecto:
Figura 9-4: Dashboard de Breeze
Verás unos archivos de vistas que ya nos generó Laravel Breeze:
Figura 9-5: Layouts incorporados por Breeze
Te recomiendo que le des una buena revisada, y verás todas las opciones que tenemos; registrar, login,
recuperar contraseña y cerrar sesión:
138
Figura 9-6: Opciones de usuario
Configurar nuestra aplicación con Laravel Breeze
Ahora que ya conocemos que es lo que tenemos, vamos aprovechar para utilizarlo y aplicarlo en nuestro
proyecto, tanto a nivel funcional, con el login, como a nivel de estilo.
Configurar ruta
Lo primero que vamos a hacer, es proteger nuestro módulo de dashboard; en las rutas mediante el middleware
que generó mediante Laravel Breeze:
Route::group(['prefix' => 'dashboard', 'middleware' => 'auth'], function () {
Route::get('/', function () {
return view('dashboard');
})->name("dashboard");
Route::resources([
'post' => App\Http\Controllers\Dashboard\PostController::class,
'category' => App\Http\Controllers\Dashboard\CategoryController::class,
]);
});
Ya con esto, si intentas ingresar sin iniciar sesión, te mandará a la página de login.
Configurar en layouts y vistas
Laravel Breeze generó múltiples archivos de vista, entre un layout, y un archivo para manejar las rutas que
vamos a conocer a continuación.
Importante notar que, al estar trabajando con código autogenerado resulta imposible explicar línea a línea como
hemos hecho hasta ahora, por lo tanto, se explicarán las partes más importantes de dichos archivos.
139
La vista de:
layouts/app.blade
Es el layout base que emplea Laravel Breeze; dentro de ella, encontraremos un fragmento de vista:
***
@include('layouts.navigation')
***
Si revisamos la página de layouts/navigation.blade.php, verás que existen unas etiquetas que comienzan con
"x-"; por ejemplo, x-nav-link; estos son componentes en Laravel, que es el otro tipo de controlador (por
mencionarlo de alguna manera) pero más flexible y sencillo; de momento, no te preocupes mucho por ellos, ya
que, el funcionamiento básico es similar al que tienen los fragmentos de vista; este componente que está
referenciando se encuentra dentro de:
views/components
140
Figura 9-7: Vistas de componentes creados por Breeze
El "x-" es el prefijo que usan los componentes en Laravel, que luego son seguidos de el nombre del componente
(nombre del archivo) si analizamos el componente en cuestión:
views/components/nav-link.blade.php
@props(['active'])
141
@php
$classes = ($active ?? false)
? 'inline-flex items-center px-1 pt-1 border-b-2 border-indigo-400 text-sm
font-medium leading-5 text-gray-900 focus:outline-none focus:border-indigo-700 transition
duration-150 ease-in-out'
: 'inline-flex items-center px-1 pt-1 border-b-2 border-transparent text-sm
font-medium leading-5 text-gray-500 hover:text-gray-700 hover:border-gray-300
focus:outline-none focus:text-gray-700 focus:border-gray-300 transition duration-150
ease-in-out';
@endphp
<a {{ $attributes->merge(['class' => $classes]) }}>
{{ $slot }}
</a>
Importante notar algunas referencias, por ejemplo, la de @props tal cual indica, estamos especificando un prop,
en este caso uno llamado active que va a ser la fuente de entrada (una variable) que pasamos desde un
elemento padre (en este caso desde la vista de nav-link).
En pocas palabras, desde la vista de navegación, que es donde incluimos el componente de nav-link, le
pasamos una variable; esto es similar a como pasamos variable entre vistas, pero así es el esquema que
tenemos al emplear los componentes.
En este punto, como recomendación que se le deja al lector es que, si no conoce Vue, puedes pausar el capítulo
y aprender algo sobre Vue, ya que, los componentes en Vue guardan muchas características con los
componentes en Laravel; en mi canal de YouTube cuento con varias listas de reproducción en donde explicamos
Vue desde cero; de igual manera, más adelante en el libro explicaremos los componentes en detalle.
Vamos a aprovechar para crear un par de enlaces para nuestras opciones CRUD:
resources\views\layouts\navigation.blade.php
<div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
<x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
{{ __('Dashboard') }}
</x-nav-link>
<x-nav-link :href="route('post.index')" :active="request()->routeIs('post.index')">
{{ __("Post") }}
</x-nav-link>
<x-nav-link :href="route('category.index')"
:active="request()->routeIs('category.index')">
{{ __("Category") }}
</x-nav-link>
</div>
142
Con :active="request()->routeIs('post.index')" inicializa el prop, el prop que vimos definido en el componente
de nav-link.blade.php.
Luego, nuestro layout, va a lucir de la siguiente manera:
resources\views\dashboard\master.blade.php
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ config('app.name', 'Laravel') }}</title>
<!-- Fonts -->
<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap"
rel="stylesheet" />
<!-- Scripts -->
@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
<div class="min-h-screen bg-gray-100 dark:bg-gray-900">
@include('layouts.navigation')
<!-- Page Heading -->
@if (isset($header))
<header class="bg-white dark:bg-gray-800 shadow">
<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
{{ $header }}
</div>
</header>
@endif
<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
@if (session('status'))
{{ session('status') }}
@endif
@yield('content')
</div>
</div>
</body>
143
</html>
En pocas palabras, reemplaza todo el contenido de la página, pero mantén la directiva del yield y el mensaje
flash; con esto:
Figura 9-8: Estilo para el layout
Ya tenemos el layout prácticamente listo; ahora, vamos a adaptar los componentes; es decir, tablas, botones,
formularios, etc.
Adaptar estilo al resto de la aplicación
Existen dos formas que pudiera recomendarte para trabajar con los estilos en Tailwind y Laravel:
1. En base a componentes de Laravel, para eso puedes fijarte en cómo están configurados los componentes
que nos trajo Laravel Breeze.
2. En base a clases en un archivo CSS.
Como no hemos trabajado con componentes en Laravel, y tampoco la estructura que tenemos actualmente,
basada en el MVC se adapta directamente a trabajar los estilos con componentes, vamos a realizar el diseño
mediante clases de CSS.
Vamos a trabajar en el archivo de:
resources\css\app.css
En donde se encuentran importados los CSS de Tailwind, mediante @import:
144
resources\css\app.css
@import 'tailwindcss/base';
@import 'tailwindcss/components';
@import 'tailwindcss/utilities';
En este archivo, es donde podemos usar las clases de Tailwind; ya que, recuerda, que Tailwind está basado en
clases principalmente, para que crees los componentes que necesites.
En los siguientes apartados, vamos a aplicar estilos a nuestros componentes, como tablas, formularios y
botones; va a ser un estilo básico y sencillo, que, por supuesto puedes tanto escalar como personalizar a tu
antojo, pero se mantendrá básico ya que, no se quiere perder el enfoque que es trabajar con Laravel.
Configurar tabla
Para nuestras tablas en los index.blade.php de posts y categorías, vamos a definir una clase:
***
<table class="table">
***
Y nuestro estilo:
resources\css\app.css
/* ******************** table */
.table {
@apply table-auto w-full
}
.table th {
@apply px-6 py-2 bg-gray-50 font-medium text-gray-500 uppercase border leading-4
tracking-widest
}
.table tr {
@apply border
}
.table td {
@apply px-6 py-4 whitespace-normal
}
/* ******************** table */
145
Configurar formulario
Para nuestros campos de formulario en los _form.blade.php de post y categorías, vamos a definir una clase:
***
<label for="">Title</label>
<input type="text" class="form-control" name="title" value="{{ old('title', $post->title)
}}">
<label for="">Slug</label>
<input type="text" class="form-control" name="slug" value="{{ old('slug', $post->slug) }}">
<label for="">Category</label>
<select class="form-control" name="category_id">
<option value=""></option>
@foreach ($categories as $title => $id)
<option {{ old('category_id', "$post->category_id") == $id ? 'selected' : '' }}
value="{{ $id }}">
{{ $title }}</option>
@endforeach
</select>
<label for="">Posted</label>
<select class="form-control" name="posted">
<option {{ old('posted', $post->posted) == 'not' ? 'selected' : '' }}
value="not">No</option>
<option {{ old('posted', $post->posted) == 'yes' ? 'selected' : '' }}
value="yes">Si</option>
</select>
<label for="">Content</label>
<textarea class="form-control" name="content"> {{ old('content', $post->content)
}}</textarea>
<label for="">Description</label>
<textarea class="form-control" name="description">{{ old('description', $post->description)
}}</textarea>
***
Y nuestro estilo:
resources\css\app.css
/* ******************** form */
.form-control {
@apply block rounded-sm shadow-sm bg-purple-50 w-full
}
146
/* ******************** form */
Configurar container
En el caso del container es más simple, ya que, tenemos una clase container, que podemos usar para colocar
nuestro contenido en un bloque y que no ocupe todo el ancho de la pantalla; el container, tienen definido los
siguientes media-queries:
(<640px) max-width: 100%;
(640px) max-width: 640px;
(768px) max-width: 768px;
(1024px) max-width: 1024px;
(1280px) max-width: 1280px;
(1536px) max-width: 1536px;
Finalmente, el código:
resources\views\dashboard\master.blade.php
***
<div class="container mx-autor">
@yield('content')
</div>
***
Configurar los botones
Vamos a definir distintas tonalidades para los botones; también, definimos un esquema básico para la estructura
llamada btn:
/* ******************** btn */
.btn {
@apply border rounded px-3 py-2 text-white font-bold inline-block transition-all
}
.btn-danger{
@apply bg-red-600 hover:bg-red-800 border-red-600
}
.btn-success{
@apply bg-green-600 hover:bg-green-800 border-green-600
}
.btn-primary{
@apply bg-blue-600 hover:bg-blue-800 border-blue-600
}
.btn-warning{
@apply bg-yellow-600 hover:bg-yellow-800 border-yellow-600
}
/* ******************** btn */
147
En las vistas de index.blade.php:
<a class="btn btn-success my-3" href="{{ route("post.create") }}">Crear</a>
**
<a class="mt-2 btn btn-primary" href="{{ route("post.edit", $p) }}">Editar</a>
<a class="mt-2 btn btn-primary" href="{{ route("post.show", $p) }}">Ver</a>
<form action="{{ route("post.destroy", $p) }}" method="post">
@method("DELETE")
@csrf
<button class="mt-2 btn btn-danger" type="submit">Eliminar</button>
</form>
En las vistas de _form.blade.php:
<button type="submit" class="btn btn-success mt-3">Send</button>
Configurar la carta
Para la carta definiremos una estructura y un color por defecto:
resources\css\app.css
/* ******************** card */
.card {
@apply p-6 rounded shadow-sm
}
.card-white {
@apply bg-white
}
/* ******************** card */
En la vista maestra o layout del módulo de gestión layout.blade.php:
<div class="container">
<div class="card card-white mt-4">
@yield('content')
</div>
</div>
Mensajes Flash
Para los mensajes de confirmación, vamos a aprovechar el diseño de cartas que creamos antes, pero, indicando
un color diferente:
resources\css\app.css
148
.card-success {
@apply bg-green-700
}
Puedes crear otros colores para mostrar mensajes de alerta según el tipo, si son errores, informativos, etc;
finalmente, usamos las clases anteriormente definidas:
<div class="container mx-auto">
@if (session('status'))
<div class="card card-success">
{{ session('status') }}
</div>
@endif
<div class="card card-white">
@yield('content')
</div>
</div>
Otros estilos
También aplicaremos estilos para los H1s y LABELs de manera global; es decir, sin clases:
resources\css\app.css
/* ******************** base */
h1{
@apply text-4xl mb-3
}
/* ******************** base */
/* ******************** form */
***
label {
@apply mt-2 block
}
Puedes ver algunas pantallas a continuación:
149
Figura 9-9: Listado
150
Figura 9-10: Formulario
Enlaces de navegación para los posts y categorías
Agregaremos los enlaces de navegación para las publicaciones y categorías en el layout definido por Breeze;
para ello, debemos de agregar el enlace en dos partes:
resources\views\layouts\navigation.blade.php
<div class="pt-2 pb-3 space-y-1">
<x-responsive-nav-link :href="route('dashboard')"
:active="request()->routeIs('dashboard')">
{{ __('Dashboard') }}
</x-responsive-nav-link>
<x-responsive-nav-link :href="route('post.index')"
:active="request()->routeIs('post.index')">
{{ __('Post') }}
</x-responsive-nav-link>
<x-responsive-nav-link :href="route('category.index')"
:active="request()->routeIs('category.index')">
{{ __('Category') }}
</x-responsive-nav-link>
</div>
151
***
<div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
<x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
{{ __('Dashboard') }}
</x-nav-link>
<x-nav-link :href="route('post.index')" :active="request()->routeIs('post.index')">
{{ __('Post') }}
</x-nav-link>
<x-nav-link :href="route('category.index')"
:active="request()->routeIs('category.index')">
{{ __('Category') }}
</x-nav-link>
</div>
El primero es para el modo responsive y el siguiente para el modo PC.
Laravel Breeze variantes en la instalación
Como vimos antes, al momento de agregar Breeze a un proyecto en Laravel, tenemos varias opciones que
escoger; en el libro usamos blade como opción para el scaffolding, pero, podemos utilizar otras; vamos a conocer
más en detalle cada una de estas opciones.
Es importante mencionar que estas explicaciones se realizan netamente como referencia y no son realizadas al
proyecto que estamos llevando a cabo en el cual, usamos la opción de blade; si utilizas las opciones de Vue o
React sobre una instalación anterior (por ejemplo, en el caso de Laravel breeze con Blade) se sobrescribirán las
instalaciones anteriores.
Vue
Si escogemos la opción de Vue, se configurarán las distintas pantallas de Breeze (Login, Registrar, Recuperar
contraseña...) empleando Vue con Laravel; para hacer esta combinación, en la cual, tendremos Laravel en el
backend y Vue en el frontend, se utiliza Laravel Inertia.
En este libro no trataremos Laravel Inertia en detalle del cual dispongo de un curso y libro que puede obtener
más información en desarrollolibre.net; pero, podemos decir que Laravel Inertia es un stack, un paquete más que
se instala en el proyecto y puedes ver su dependencia en el package.json, que permite utilizar Vue en las vistas y
Laravel a nivel del servidor sin necesidad de utilizar una Rest Api; con Inertia, es posible crear fácilmente webs
de tipo SPA.
Si ejecutamos:
$ php artisan breeze:install vue
Veremos que se crean archivos Vue para manejar cada una de las vistas:
resources\js\Pages\
152
Figura 9-11: Componentes en Vue creados por Laravel Breeze
A nivel de las rutas, veremos una nueva importación y también fueron sobrescritas las rutas anteriores:
routes\web.php
use Inertia\Inertia;
Al momento de entrar en cualquiera de las páginas creadas por Breeze, no notaremos ningún cambio visual:
153
Figura 9-12: Ventana de Login con Laravel Breeze
Pero veremos que las peticiones son manejadas a nivel de peticiones HTTP y no recargando toda la página:
154
Figura 9-13: Petición para obtener el componente de login
Al momento de ingresar a las páginas generadas por Laravel Breeze, recuerda tener ejecutado el comando de:
$ npm run dev
React
En el caso de React, es exactamente el mismo proceso que con Vue; al ejecutar el comando de:
$ php artisan breeze:install react
Se generarán las páginas y componentes, pero esta vez de React y también se instalará Laravel Inertia para
comunicar React con Laravel:
155
Figura 9-14: Componentes en React creados por Laravel Breeze
Código fuente del capítulo:
https://github.com/libredesarrollo/curso-laravel-primeros-pasos/releases/tag/v0.3
156
Manejo de roles
Hay muchas formas de otorgar privilegios a un usuario para que tengan distintos roles en una aplicación web; si
es una aplicación grande, o con tareas bien definidas que inclusive puede cambiar en el tiempo; por ejemplo, si
tuviéramos algo como:
1. Administrador - acceso total (gestión)
2. Editor - acceso de creador y editor (gestión)
3. Revisador - acceso editor (gestión)
4. Regular - acceso lector (web)
Por definir una estructura típica, y donde web, es el módulo de cara al usuario final y el de gestión es el que
hemos creado hasta ahora (el dashboard) pudiéramos considerar usar un paquete como el siguiente:
https://spatie.be/docs/laravel-permission/v5/introduction
Que nos permite un completo control con roles y permisos que inclusive podemos gestionar desde la base de
datos.
En el caso de la aplicación que queremos construir, no sería necesario este enfoque; ya que, vamos a tener
solamente dos tipos de usuarios:
1. Administrador.
2. Regular.
Con los accesos que definimos antes; para esto, no necesitamos instalar nada adicional, si no, manipular y
adaptar la estructura que creó Laravel Breeze, empleando un sistema de roles para cada tipo de usuario, que
luego verificamos en nuestras rutas mediante un middleware.
Caso práctico
Definir roles
Vamos a definir una nueva columna para el rol en nuestra relación de usuarios. En este escenario tenemos dos
caminos:
Creamos una nueva migración para alterar la tabla de usuarios:
//php artisan make:migration addRolToUsersTable
<?php
// ***
public function up(): void
{
Schema::table('users', function (Blueprint $table) {
157
$table->enum('rol',["admin", "regular"])->default("regular");
});
}
public function down(): void
{
Schema::table('users', function (Blueprint $table) {
$table->dropColumn('rol');
});
}
};
Y ejecutas el comando de migrate, para ejecutar la migración:
$ php artisan migrate
O modificamos la migración de usuarios:
<?php
// ***
public function up(): void
{
Schema::create('users', function (Blueprint $table) {
$table->id();
$table->string('name');
$table->string('email')->unique();
$table->timestamp('email_verified_at')->nullable();
$table->string('password');
$table->enum('rol',["admin", "regular"])->default("regular");
$table->rememberToken();
$table->timestamps();
});
}
/**
* Reverse the migrations.
*
* @return void
*/
public function down(): void
{
Schema::dropIfExists('users');
}
};
158
Y ejecutamos un:
$ php artisan migrate:refresh
Cual opción escojas depende de ti; particularmente considero útil tener en solo una migración toda la estructura
de su tabla, pero no siempre esto es posible como comentamos en un capítulo anterior; para efectos del libro,
vamos a seleccionar la segunda opción; ya que, al tener pocas migraciones y sin datos reales, podemos ejecutar
nuevamente todas las migraciones.
Crear el middleware para la verificación de un usuario administrador
Creamos un middleware con el siguiente comando:
$ php artisan make:middleware UserAccessDashboardMiddleware
Con el siguiente contenido:
<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
class UserAccessDashboardMiddleware
{
public function handle(Request $request, Closure $next)
{
if (Auth::user()->isAdmin())
return $next($request);
return redirect("/");
}
}
Alteramos el modelo de usuarios para definir el método anterior:
<?php
class User extends Authenticatable
{
***
public function isAdmin(): bool
{
return $this->rol == "admin";
159
}
}
Creamos un par de usuarios mediante la opción de registrarse, uno para cada rol:
Figura 9-15: Crear usuarios de prueba
Y registramos directamente en las rutas agrupadas:
use App\Http\Middleware\UserAccessDashboardMiddleware;
Route::group(['prefix' => 'dashboard', 'middleware' => ['auth',
UserAccessDashboardMiddleware::class]], function () {
Route::resources([
'post' => App\Http\Controllers\Dashboard\PostController::class,
'category' => App\Http\Controllers\Dashboard\CategoryController::class,
]);
});
Con esto, protegemos el módulo dashboard con autenticación y el rol de admin; puedes intentar ingresar al
dashboard con un usuario regular y no te dejará, te debe de redireccionar al index de tu aplicación; solamente
podrás acceder mediante un usuario con rol de admin.
Código fuente del capítulo:
https://github.com/libredesarrollo/curso-laravel-primeros-pasos/releases/tag/v0.4
160
Capítulo 10: Operaciones comunes en Eloquent
(ORM)
Estos son algunos de los querys builder que yo he empleado en mis proyectos en Laravel, y quiero traer algunos
para que tengas una referencia un poco más completa de lo que puedes hacer; nuevamente el propósito de
estos query builders, es que puedes conocer cómo realizar operaciones que en algún momento vas a necesitar y
su propósito en el siguiente capítulo es netamente referencial.
Como recomendación, prueba estos queréis, ver el SQL generado mediante el método de toSql() que ya vamos
a evaluar.
En Laravel, el ORM que empleamos se llama Eloquent y está ligado a los modelos, por ejemplo:
Post::where('posts.id',$id);
https://laravel.com/docs/master/eloquent
Pero, también podemos emplear consultas sin emplear modelos, empleando el Query Builder:
DB::table('posts')->where('posts.id',$id);
Ambos esquemas permiten emplear la mayoría de los métodos y son equivalentes para la mayoría de los
ejemplos que vamos a ver en este apartado:
https://laravel.com/docs/master/queries
Para estos ejercicios, te puedes apoyar en Tinker, para probar las consultas desde la línea de comandos:
$ php artisan tinker
Ver el SQL
Si quieres ver el SQL generado de una consulta, sin importarnos que complejo sea, en vez de indicar el método
de get(), find(), first() o cualquier otra para obtener los datos, sustitúyelo con el método de:
DB::table('posts')->toSql();
// "select * from "posts""
DB::table('posts')->where('id','>',5)->toSql()
// "select * from "posts" where "id" > ?"
O en Eloquent:
$post = Post::where('id','>',5)->toSql()
// "select * from "posts" where "id" > ?"
161
Joins
Los joins, son una estructura de lo más útil con la cual podemos combinar distintos campos referenciados por un
campo en común; usualmente la clave foránea:
Post::join('categories', 'categories.id', '=', 'posts.category_id')->
select('posts.*', 'categories.title as category')->
orderBy('posts.created_at', 'desc')->paginate(10);
Post::join('categories','categories.id','=','posts.category_id')->select('posts.*','categor
ies.id as c_id')->get()
En el ejemplo anterior, puedes ver que de manera demostrativa, se emplean otros métodos como el de select() u
orderBy(); ya que, usualmente para realizar las consultas, se emplean varios de estos métodos dependiendo de
lo que queramos realizar, usualmente podemos colocar estos métodos en cualquier parte de la consulta (salvo si
estamos agrupando) pero deben de estar antes de los métodos que resuelven el resultado, es decir, antes del
get(), find() o similares.
Recuerda que tenemos distintos tipos de joins, y en Laravel, podemos emplear cada uno de ellos:
https://laravel.com/docs/master/queries#joins
Ordenación
Si no quieres ordenar por el id, que es el valor por defecto, puedes ordenar por una columna en particular:
Post::join('categories', 'categories.id', '=', 'posts.category_id')->
select('posts.*', 'categories.title as category')->
orderBy('posts.created_at', 'desc')->paginate(10);
Where o orWhere anidados
En Laravel, tenemos todos los tipos de where; pero, sabemos que cuando empleamos un where y un orWhere al
mismo nivel sin agruparlos, lo devuelto, no es lo que esperamos; para poder agrupar mediante un orWhere o
similares:
$posts = Post::join('categories', 'categories.id', '=', 'posts.category_id')
->select('posts.*', 'categories.title as category', 'categories.slug as c_slug')
->where('categories.slug', $category_slug)
->where('posted', "yes")
->where(function ($query) {
$query->orWhere('type', 'post')
->orWhere('type', 'courses')
->orWhere('type', 'group');
})
->orderBy('posts.created_at', 'desc')
->paginate(10);
162
Si quisiéramos pasar parametros, puedes emplear el método de use como puedes ver en el siguiente ejemplo:
$category_id = 1;
Post::where('id', '>=', 1)->where(function ($query) use ($category_id) {
dd($category_id);
$query->where('category_id', $category_id)->orWhere('posted', 'yes');
})->get();
WhereIn y WhereNotInt
También tenemos el método para buscar por un array de ids:
$ids = array( 1, 2, 3, 4, 5, 6 );
$posts = Post::whereIn('posts.id',$ids);
$posts = Post::whereNotIn('posts.id',$ids);
Obtener un registro
Sin importar la consulta, si solamente quieres obtener un solo registro en el resultado:
$posts = Post::where('slug', $slug)->first();
Limitar la cantidad de registros
Si quieres limitar la cantidad de registros:
$posts = Post::limit(3)->get();
También podemos indicar un bloque o página en específico:
$posts = Post::limit(3)->offset(2)->get()
Ambas funciones son útiles para crear una paginación personalizada.
Cantidad
Para obtener la cantidad de registros, podemos emplear el método de count():
Post::limit(2)->offset(2)->get()->count()
Obtener registros aleatorios
Si quieres obtener registros de manera aleatoria:
163
$posts = Post::where(<>)->inRandomOrder()->get();
Lazy Loading y Eager Loading
En Laravel empleando el ORM de Eloquent, existen dos formas de manejar las relaciones entre tablas:
Carga ansiosa/Eager Loading: La carga ansiosa le permite recuperar el modelo principal junto con los modelos
relacionados, para esto, se emplea el método with() para especificar las relaciones.
cargarse por adelantado, lo que reduce el número de consultas a la base de datos y mejora
actuación:
// Eager loading example
$users = User::with('posts')->get();
Carga diferida/Lazy Loading: la carga diferida carga los modelos relacionados sólo cuando se acceden a ellos:
$posts = Post::all();
foreach ($posts as $p) {
$categories = $p->categories; // Hace una consulta a la BD por cada post
}
En el ejemplo anterior, se consulta la entidad de posts a la base de datos solamente cuando se hace la operación
de:
$p->categories
A diferencia del eager loading que trae todos los posts y categorías en una sola consulta; el lazy loading puede
acarrear el problema de consulta N+1, donde se ejecutan consultas adicionales al acceder a temas relacionados.
En el ejemplo anterior, N corresponde a todas las consultas para obtener las categorías y el 1 equivale a la
consulta inicial para obtener las publicaciones; cuál enfoque emplear, depende de lo que quieras hacer con los
datos, si no vas a emplear los datos relacionados, puedes emplear el enfoque de lazy loading, si vas a emplear
los datos relacionados, puedes emplear el eager loading.
Ventaja de la carga ansiosa
Mejora del rendimiento: la carga ansiosa reduce las consultas a la base de datos al recuperar todos los datos
requeridos en una consulta y con esto, se evita el problema N+1.
Desventaja de la carga ansiosa
Mayor tiempo de consulta: Al poder generar consultas complejas, puede requerir un mayor tiempo de
procesamiento.
164
Ventaja de la carga diferida
Uso más eficiente de los recursos del servidor y con esto, consultas más rápidas, al cargar solamente los
modelos relacionados cuando sean necesarios.
Desventaja de la carga diferida
Problema de consulta N+1: Dependiendo del tratamiento que se vaya a realizar con los datos, puede traer
problemas de rendimiento como el N+1 y con esto sobrecargar el servidor.
Serialización
La serialización se refiere al proceso de convertir objetos o estructuras de datos a un formato en particular para
poder ser más fácilmente transmitidos y con esto consumidos, el formato JSON es un buen ejemplo de esto, que
es el formato por excelencia empleado al momento de crear una Rest Api como veremos más adelante.
Mediante Eloquent, podemos convertir los datos obtenidos mediante una consulta a formato JSON:
$post = Post::find(1);
$json = $post->toJson();
O array:
$post = Post::find(1);
$array = $post->toArray();
Restricciones de consulta
Con Eloquent, podemos realizar toda clase de restricciones que acompañan la consulta y con esto poder
recuperar datos específicos, entre las principales tenemos:
● "where": agrega una cláusula básica where a la consulta.
● "orWhere": agrega una cláusula "or" where a la consulta.
● "whereIn": agrega una cláusula where in a la consulta.
● "whereBetween": agrega una cláusula where between a la consulta.
● "orderBy": ordena los resultados de la consulta por una columna especificada.
● "limit": limita el número de registros devueltos por la consulta.
● "offset": Especifica un DESPLAZAMIENTO desde donde comienza a devolver los datos.
Limit y Offset
Puede utilizar los métodos skip y take para limitar la cantidad de resultados devueltos por la consulta o para
omitir una cantidad determinada de resultados en la consulta:
$posts = Post::skip(10)->take(5)->get();
Alternativamente, puede utilizar los métodos de límite y compensación. Estos métodos son funcionalmente
equivalentes a los métodos tomar y omitir, respectivamente:
165
$posts = Post::offset(10)->limit(5)->get();
Todas estas restricciones se pueden emplear en conjunto como hemos visto en los ejemplos anteriores:
$users = Post::where('type', 'post')
->orWhere('type', 'book')
->orderBy('created_at', 'desc')
->limit(10)
->get ();
Estos son algunos query builders que puedes tener en mente al momento de crear tus controladores y demás
componentes; sin embargo, recuerda que es netamente referencial y en Laravel aún existen muchos más
métodos para operar con la base de datos; si quieres obtener más información:
https://laravel.com/docs/master/queries
Optimización de consultas
La optimización es fundamental en cualquier sistema, y en Laravel no es la excepción, usualmente en los
listados, no usamos todos los campos provistos por la entidad, dando como resultado un mayor procesamiento
de los datos que se traduce en una respuesta más lenta; por ejemplo, en nuestra aplicación, para el listado, no
necesitamos el contenido de la publicación en el listado, que es el campo de mayor tamaño en la entidad:
<td>
{{ $p->id }}
</td>
<td>
{{ $p->title }}
</td>
<td>
{{ $p->posted }}
</td>
<td>
{{ $p->category->title }}
</td>
Por lo tanto, podemos prescindir de este campo dejando la consulta como:
Post::with('category')->select('id', 'title', 'date', 'posted', 'type', 'language',
'url_clean', 'category_id')
Otro ejemplo, supongamos que tenemos las siguientes relaciones:
class Book extends Model
{
166
use HasFactory;
protected $fillable = ['title', 'subtitle', 'date', 'url_clean', 'description',
'content', 'image', 'path', 'page', 'posted', 'price', 'price_offers', 'post_id',
'user_id'];
public function post()
{
return $this->belongsTo(Post::class)
->select(array('id', 'url_clean', 'title', 'category_id'));
}
}
class Post extends Model
{
protected $fillable = ['title', 'url_clean', 'content', 'category_id', 'posted',
'description', 'final_content', 'aux_content', 'web_content', 'image', 'path', 'date',
'type', 'post_id_language', 'language'];
public function category()
{
return $this->belongsTo(Category::class)
->select(array('id', 'url_clean', 'title'));
}
}
class Category extends Model
{
protected $fillable = ['title', 'url_clean', 'image', 'body', 'body_en'];
}
Si tenemos un listado como el siguiente:
{{ $b->id }}
{{ $b->title }}
{{ $b->subtitle }}
{{ $b->date->format('d-m-Y') }}
{{ $b->post->category->title }}
{{ $b->posted }}
Podemos prescindir del campo content del book al igual que cargar las relaciones de post y categoría:
$books = Book
::with('post', 'post.category')
->select('id', 'title', 'subtitle', 'date', 'posted',
'post_id')->orderBy($this->sortColumn, $this->sortDirection);
167
Recuerda siempre colocar desde la relación principal sus relaciones, específicamente el campo de post_id para
que pueda traer mediante el with las relaciones asociadas.
Capítulo 11: Componentes
Los componentes son similares a los layouts y los fragmentos de vista; pero con esteroides, ya que, los mismos
permiten la definición de pase de parámetros (como los fragmentos de vista), definición de slot, para colocar
contenido clave, pase de atributos, llamados a métodos, entre otras opciones.
La idea central de los componentes, es que, con las mismas podemos hacer verdaderas micro aplicaciones,
queriendo decir con micro aplicaciones, pequeños "componentes" que realizan una función en particular, cómo
listar un post; pero, desde este listado, el mismo componente es capaz de brindar opciones sobre ese mismo
componente, como por ejemplo, asignarlo a un favorito, remover un favorito, agregarlo a una clasificación, indicar
si quieres mostrarlo en base a una condición y tareas de este tipo; la ventaja fundamental con respecto a los
fragmentos de vista, es que, toda esta lógica de opciones está dentro del componente, y no depende de
componentes padres (como sucedería con los fragmentos de vista).
Hay dos enfoques para escribir componentes:
1. Componentes basados en clases, que constan de una vista y una clase.
2. Componentes anónimos, que constan solamente de una vista.
Para crear un componente, lo podemos hacer mediante artisan con:
$ php artisan make:component <TuComponente>
Al cual puedes indicar la jerarquía de carpetas, y la opción de --view para indicar que quieres un componente con
solamente una vista.
Esta lección es mayormente práctica, ya que, la idea es introducir algunas funcionalidades que tienen este tipo
de elementos en base a ejemplos.
Estructura inicial
En este apartado, nos interesa construir parte del módulo web de cara al usuario final, es decir, en donde vamos
a tener nuestros listados de posts para que sean consumidos por un internauta.
Vamos a emplear los componentes en conjunto con un controlador, así que, vamos a crear ese controlador con:
$ php artisan make:controller blog/BlogController -m Post
Y va a tener la siguiente estructura:
app\Http\Controllers\blog\BlogController.php
<?php
168
namespace App\Http\Controllers\blog;
use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\View\View;
class BlogController extends Controller
{
function index()
{
$posts = Post::paginate(2);
return view('blog.index', compact('posts'));
}
function show(Post $post){
return view('blog.show', ['post' => $post]);
}
}
Dejando solamente el método de index(), para el listado y show() para el detalle.
Sus rutas serán como:
routes\web.php
use App\Http\Controllers\web\BlogController;
***
Route::group(['prefix' => 'blog'], function () {
Route::controller(BlogController::class)->group(function () {
Route::get('', [BlogController::class, 'index'])->name('blog.index');
Route::get('detail/{post}', [BlogController::class, 'show'])->name('blog.show');
});
});
Las rutas están compuestas de un agrupamiento para el prefijo, uno para el controlador y, finalmente las rutas.
Componentes anónimos: Vista de listado
Los componentes anónimos son aquellos que solamente constan de una vista; para crear un componente de
este tipo:
$ php artisan make:component blog.post.index --view
169
Y con esto, se creará un componente de vista en:
resources/views/components/blog/post/index.blade.php
Al cual, agregaremos el siguiente contenido:
resources\views\components\blog\post\index.blade.php
<div>
@foreach ($posts as $p)
<div class="card card-white mt-2">
<h3>{{ $p->title }}</h3>
<a href="{{ route('blog.show', $p) }}">Go</a>
<p>{{ $p->description }}</p>
</div>
@endforeach
</div>
Para usarla desde:
resources\views\blog\index.blade.php
Con el siguiente contenido:
resources\views\blog\index.blade.php
@extends('blog.master')
@section('content')
<x-blog.post.index :posts="$posts"/>
@endsection
El layout luce como:
resources\views\blog\master.blade.php
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ config('app.name', 'Laravel') }}</title>
170
<!-- Fonts -->
<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap"
rel="stylesheet" />
<!-- Scripts -->
@vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
<div class="min-h-screen bg-gray-100 dark:bg-gray-900">
<!-- Page Heading -->
@if (isset($header))
<header class="bg-white dark:bg-gray-800 shadow">
<div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
{{ $header }}
</div>
</header>
@endif
<!-- Page Content -->
<main>
<div class="container mx-auto">
@yield('content')
</div>
</main>
</div>
</body>
</html>
Como puedes ver, consumimos el contenido con una estructura similar al que hacemos con el método de view(),
y para pasar parámetros, lo hacemos a la “manera Vue” con dos puntos ":" seguido del nombre de la variable:
resources\views\components\blog\post\index.blade.php
<div>
@foreach ($posts as $p)
<div class="card mt-2">
<h3>
{{ $p->title }}
</h3>
<a href="{{ route('blog.show', $p) }}">Go</a>
<p>{{ $p->description }}</p>
</div>
171
@endforeach
{{ $posts->links() }}
</div>
Veremos:
172
Figura 11-1: Listado de publicaciones
Es importante que la estructura que mostramos en este apartado, en donde el controlador emplea un
componente anónimo para renderizar el contenido es netamente demostrativo, es decir, no es una estructura que
debas de seguir exactamente igual a lo mostrado, aquí también hubieras podido emplear solamente el
controlador con su vista asociada que devuelva el listado de publicaciones, tal cual hicimos antes en el
dashboard o emplear un componente con clase (como veremos más adelante).
La misma lógica que explicamos antes, se aplica al layout, si revisamos una vista existente que se creó al
momento de instalar Breeze:
resources\views\dashboard.blade.php
<x-app-layout>
***
Que puedes apreciar que está empleando un componente como layout en vez de un template maestro como
hicimos nosotros en el dashboard, ambos esquemas son correctos y puedes emplear el de tu preferencia, el uso
de los componentes tiene su equivalente en los controladores, los componentes son una funcionalidad que es
relativamente nueva en el desarrollo en Laravel que surgieron a partir de la versión 7 y con esto queda más clara
la idea de que existe un equivalente en el uso de controladores y componentes y es el desarrollador el que debe
de escoger cuál emplear según la situación; como hemos comentado, este escrito muestra ejemplos prácticos
para evidenciar las funcionalidades principales de Laravel, por lo tanto, no siempre se se siguen las mejores
prácticas si no las necesarias para mostrar estas funcionalidades.
Slot
Los slots permiten adicionar contenido extra HTML/PHP a nuestro componente; con los slots, podemos
personalizar fácilmente los componentes indicando mediante secciones aquellos apartados a los cuales
queramos colocar dicho contenido extra.
Por ejemplo, en un componente de listado que luce como el siguiente:
173
Figura 11-2: Listado de publicaciones de ejemplo
Puede interesarnos personalizar el título, footer y tal vez un apartado para agregar contenido adicional (como
enmarcamos en la imagen anterior); cada uno de estos apartados, pueden ser un slot.
● Podemos usar un listado como el anterior para indicar los últimos posts.
174
● También, para construir un listado en base a una categoría establecida, en este caso, cambiamos el título
y el footer para mostrar información sobre la categoría y en el contenido extra colocamos los cursos
relacionados con las categorías.
Claro está, estos son solamente dos ejemplos, lo importante es notar que podemos crear múltiples listados
usando el mismo componente y actualizar las secciones para personalizar la experiencia en cada uno de los
slots.
Apartados como el anterior, se pueden construir fácilmente usando los componentes y los slots.
Los slots pueden ser empleados de diversas maneras, veamos cuales son.
Slot por defecto
Por defecto, podemos usar el componente como si fuera un elemento HTML, indicando una etiqueta de apertura
y otra de cerrado; en el medio (entre las etiquetas), colocamos el contenido del slot por defecto:
resources\views\blog\index.blade.php
@extends('blog.master')
@section('content')
<x-blog.post.index :posts="$posts">
Posts
</x-blog.post.index >
@endsection
Luego, desde el componente, consumimos este contenido mediante una variable llamada slot:
resources\views\components\blog\post\index.blade.php
<div>
<h1>{{ $slot }}</h1>
@foreach ($posts as $p)
<div class="card mt-2">
<h3>
{{ $p->title }}
</h3>
<a href="{{ route('blog.show', $p) }}">Ir</a>
<p>{{ $p->description }}</p>
</div>
@endforeach
{{ $posts->links() }}
</div>
175
Veremos el título de "Posts" en un H1:
Figura 11-3: Listado de publicaciones y slot por defecto
176
Slots con nombre
Como se mencionó antes, muchas veces es necesario definir múltiples apartados en un componente para definir
el contenido extra, por lo tanto, con los componentes podemos tener estructuras complejas pasando múltiples
datos para personalizar los mismos; para ello, podemos definir slots con nombres; el nombre es usado tanto para
declarar el slot como su contenido.
En este ejemplo, definimos tres slots con nombre, "header", "footer" y "extra" respectivamente:
resources\views\blog\index.blade.php
@extends('blog.master')
@section('content')
<x-blog.post.index :posts='$posts'>
Post List
@slot('footer')
Footer
@endslot
@slot('extra')
Extra
@endslot
</x-blog.post.index>
@endsection
A nivel de la vista, se usan los nombres de los slots como variables:
resources\views\components\blog\post\index.blade.php
<div>
<br>
<h1>{{ $slot }}</h1>
@if (isset($header))
<h1>{{ $header }}</h1>
@endif
@foreach ($posts as $p)
<div class="card card-white mt-2">
<h3>{{ $p->title }}</h3>
<a href="{{ route('blog.show', $p) }}">Ir</a>
<p>{{ $p->description }}</p>
177
</div>
@endforeach
<br>
@isset($extra)
<h1>{{ $extra }}</h1>
@endisset
<h1>{{ $footer }}</h1>
{{ $posts->links() }}
</div>
Y veremos:
178
Figura 11-4: Listado de publicaciones, slot por defecto y slot con nombre
En el ejemplo anterior, puedes ver que desde el componente, se verifica si se está suministrando el slot con
nombre mediante condicionales y la directiva isset(), por lo tanto, dependiendo de cómo definas tus slots,
179
pueden que sean opcionales, como en el caso del slot para el título y extra u obligatorios como en el caso del slot
del footer.
Slots con nombre en una línea
Muchas veces no es necesario pasar un contenido HTML completo en los slots, si no, solamente un valor, un
texto o un número; en estos casos, podemos usar la siguiente sintaxis:
resources\views\web\blog\index.blade.php
<x-blog.post.index :posts="$posts" title='Listado inicial'>
@slot('other', 'Extra')
</x-blog.post.index>
Y desde el componente:
resources\views\components\web\blog\post\index.blade.php
No hay nada que cambiar y veremos la misma imagen mostrada anteriormente.
Componentes con clases: Vista de detalle
Este componente emplea una clase para trabajar con la vista del componente; por lo tanto, la comunicación no
sería entre la vista del controlador y el componente, si no, será entre la vista del controlador, la clase componente
y esta con su vista; dando un mejor control para realizar cualquier proceso programado mediante funciones:
Figura 11-5: Listado de publicaciones, slot por defecto y slot con nombre
Creamos el componente:
$ php artisan make:component blog/post/Show
Y con esto, se creará un componente de vista en:
180
app/View/Components/blog/post.blade.php
En la vista del componente, colocaremos:
resources\views\components\blog\show.blade.php
<div class="card card-white">
<h1>{{ $post->title }}</h1>
<span>{{ $post->category->title }}</span>
<hr>
{{ $post->content }}
</div>
Y de clase en:
app\View\Components\blog\Show.php
<?php
namespace App\View\Components\web\blog\post;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
class Show extends Component
{
public function __construct()
{
}
public function render(): View|Closure|string
{
return view('components.blog.post.show');
}
}
Es una clase típica, con un método de render(), la cual, carga el vista del componente; tenemos un método de
__construct(), la cual empleamos para inicializar dicho componente.
En el método de show() del controlador, recordemos que, estamos pasando el detalle del post que vamos a
colocar el componente:
181
resources\views\blog\show.blade.php
@extends('blog.master')
@section('content')
<x-blog.show :post="$post" />
@endsection
Con esto, tenemos que definir la variable de post que le estamos pasando desde la vista de show del
controlador, en la clase del componente, esto lo hacemos creando una propiedad en dicha clase, que tenemos
que inicializar en el método constructor; finalmente, el código queda como:
class Show extends Component
{
public $post;
public function __construct($post)
{
$this->post = $post;
}
public function render(): View|Closure|string
{
return view('components.blog.post.show');
}
}
O un equivalente:
app\View\Components\blog\Show.php
class Show extends Component
{
// public $post;
public function __construct(public Post $post)
{}
public function render(): View|Closure|string
{
return view('components.blog.post.show');
}
}
Y veremos:
182
Figura 11-6: Vista de detalle
Es importante aclarar que cuando se está consumiendo el componente con clase desde alguna vista:
<x-blog.show :post="$post" />
El componente que se está consumiendo es la clase:
app\View\Components\blog\Show.php
Y no su vista como en los ejemplos anteriores; otro ejemplo de esto ocurre con el componente creado por Breeze
llamado AppLayout.php, que puedes ver que en algunas vistas consume el componente como:
resources\views\dashboard.blade.php
<x-app-layout>
***
Cuyo componente con clase está en:
app\View\Components\AppLayout.php
Y su layout está en otra carpeta:
app\View\Components\AppLayout.php
class AppLayout extends Component
{
183
public function render(): View
{
return view('layouts.app');
}
}
Esto es importante ya que, si por ejemplo, movemos la vista del componente a otra ubicación:
resources\views\components\blog\post\show.blade.php
Ahora, pudiéramos consumir solamente la vista del componente, convirtiendo al componente con clase a con
componente anónimo:
<x-blog.post.show :post="$post" />
Es decir, ya la clase componente no estuviera utilizándose; para utilizarla, tendrias que actualizar las rutas en la
definición de la clase (crear la carpeta de post y colocas la clase dentro de la misma y actualizar el namespace);
en resumen, la estructura de carpetas que tenga la clase componente debe ser la misma que la usada para
consumir el componente desde alguna vista.
Invocar métodos
La ventaja de emplear componentes con clases, es que, podemos aprovechar la clase del componente para
hacer cualquier tipo de conexión mediante métodos; por lo tanto, conectarnos a la base de datos, enviar un
email, entre otro tipo de operaciones, lo podemos definir aquí; vamos a crear un método de ejemplo en la clase
del componente:
class Detail extends Component
{
***
public function changeTitle(): void
{
$this->post->title = "New Title";
}
***
}
Que lo único que hace es cambiar el título del post, pero, nuevamente puede hacer cualquier otro tipo de
operación; y en la vista del componente:
{{ $changeTitle() }}
<h1>{{ $post->title }}</h1>
<p>{{ $post->created_at }}</p>
<p>{{ $post->content }}</p>
Es importante que la impresión del título este después del llamado al método definido anteriormente, y veremos:
184
Figura 11-7: Título cambiado desde el método
Importante notar que, antes del nombre del método, debes de colocar el signo de dólar $.
También es posible retornar valores e imprimir desde la vista:
public function changeTitle(): void
{
$this->post->title = "New Title";
return true;
}
Pasar parámetros a los componentes
Como presentamos antes, usando la siguiente sintaxis:
resources\views\web\blog\index.blade.php
@extends('web.layout')
@section('content')
<x-blog.index :posts="$posts" title='List'>
<h1>Listado principal de post</h1>
</x-blog.index>
@endsection
Podemos pasar parámetros a los componentes, ya sea con valores fijos:
title='List'
O empleando variables o cualquier otra expresión que deba ser evaluado:
:posts="$posts"
Para consumir el parámetro, se hace como si fuera una variable:
resources\views\components\web\blog\post\index.blade.php
185
***
<h3>{{ $title }}</h3>
***
Mezclar atributos
Otro factor muy importante es la los atributos; con los atributos hacemos mención al atributo de las clases,
nombre, id entre otros incluyendo los personalizados que podemos establecer en el componente y al momento
de crear la instancia del componente.
A nivel del componente, podemos definir cualquier cantidad de atributos como si se tratara de un elemento HTML
cualquiera:
resources\views\components\web\blog\post\show.blade.php
<div class="bg-red-100" id="Detail">
<h1>{{ $post->title }}</h1>
<p>{{ $post->created_at }}</p>
<p>{{ $post->content }}</p>
</div>
La novedad viene en que, desde la vista de donde instanciamos el componente, podemos especificar atributos
adicionales los cuales, debemos de especificar desde el componente como deben de ser tratados. Por ejemplo.
Si desde la instancia del componente queremos usar clases (u otros atributos) específicos:
resources\views\web\blog\show.blade.php
@extends('web.layout')
@section('content')
<x-blog.show class="bg-red-100" :post="$post"/>
@endsection
Desde el componente, tenemos la variable llamada $attributes junto con el método de merge() para mezclas los
atributos establecidos especificados desde la instancia del componente:
resources\views\components\web\blog\post\show.blade.php
<div {{ $attributes->merge(['class' => '']) }}>
<h1>{{ $post->title }}</h1>
<p>{{ $post->created_at }}</p>
<p>{{ $post->content }}</p>
</div>
Y tendremos:
186
<div class="bg-red-100">
***
También, podemos especificar atributos por defecto en el componente, para eso, lo pasamos como siguiente
parámetro del método merge():
resources\views\components\web\blog\post\show.blade.php
<div {{ $attributes->merge(['class' => 'my-5']) }}>
<h1>{{ $post->title }}</h1>
<p>{{ $post->created_at }}</p>
<p>{{ $post->content }}</p>
</div>
Y tendremos:
<div class="my-5 bg-red-100">
***
O usar distintos atributos:
resources\views\components\web\blog\post\show.blade.php
<div {{ $attributes->merge(['other-attr' => 'data1']) }} {{ $attributes->merge(['class' =>
'my-list']) }}>
<h1>{{ $post->title }}</h1>
<p>{{ $post->created_at }}</p>
<p>{{ $post->content }}</p>
</div>
También lo puedes simplificar de la siguiente manera:
resources\views\components\web\blog\post\show.blade.php
<div {{ $attributes->merge([ 'class' => 'my-5', 'other-attr' => 'data1' ]) }}>
***
Y tendremos:
<div other-attr="data1" class="my-5 bg-red-100">
***
Si desde la instancia del componente, pasamos el atributo personalizado llamado other-attr:
resources\views\web\blog\show.blade.php
<x-blog.show :post="$post" class="bg-red-100" other-attr="data2"/>
187
Se sobrescribe por el que está definido a nivel del componente y no se agrega como en el caso de las clases:
<div other-attr="data2" class="my-5 bg-red-100">
***
Lo mismo sucede con otros atributos por ejemplo, el id.
Además de atributos personalizados, podemos usar el de las clases, con las cuales, inclusive podemos
establecer clases u otros atributos en base a una condición:
resources\views\components\web\blog\post\show.blade.php
<div {{ $attributes->class(['my-5', 'bg-red-100' => true]) }}>
***
En donde el true establecido para la clase bg-red-100 puede ser una variable u otra.
Y tendremos:
<div other-attr="data2" class="my-5 bg-red-100">
***
Finalmente, puedes usar todo en conjunto de la siguiente forma:
resources\views\components\web\blog\post\show.blade.php
<div {{ $attributes->class(['p-4', 'bg-red' => true])->merge(['other-attr' => 'data1']) }}>
<h1>{{ $post->title }}</h1>
<p>{{ $post->created_at }}</p>
<p>{{ $post->content }}</p>
</div>
Y tendremos:
<div other-attr="data2" class="my-5 bg-red-100">
***
Props
Hemos visto diversas formas de pasar datos a los componentes de Laravel; por defecto, los atributos y variables
son registrados en una variable especial de Laravel llamada "bag"; para ver esto en detalle, podemos hacer el
siguiente ejercicio:
resources\views\components\alert.blade.php
{{ $attributes }}
188
Y usamos el componente anterior en alguna parte de la aplicación, por ejemplo, en la vista de detalle del post:
resources\views\web\blog\show.blade.php
@section('content')
<x-alert type="error" :message="$post->title" class="mb-4" />
***
Y veremos:
type="error" message="6QwfKjmIuC5Gy1AMkNYf 2" class="mb-4"
Que aparecen todos los datos que estamos pasando al componente, tanto de atributos como es el caso de las
clases, como de otros datos como el de message; todos estos datos se almacenan en un atributo especial de
Laravel llamado "bag"; puedes provocar un error:
resources\views\components\alert.blade.php
{{ $attributes->message }}
Y verás que el error refleja sobre un atributo llamado bag:
Undefined property: Illuminate\View\ComponentAttributeBag::$message
Es aquí donde entra la directiva props; todos los atributos declarados en @props se encontrarán como datos
del componente y no como parte del "attribute bag".
Al querer usar los @props, debemos de colocarlos al inicio del componente:
resources\views\components\alert.blade.php
@props(['type' => 'info', 'message'])
<div {{ $attributes->merge(['class' => 'alert alert-' . $type]) }}>
{{ $message }}</div>
Y tendremos:
<div class="alert alert-error mb-4">6QwfKjmIuC5Gy1AMkNYf 2</div>
Puedes ver que los datos definidos como props ya no forman parte de la variable $attributes.
También puedes asignar valores por defecto a los props:
@props(['type' => 'info', 'message'])
189
Obtener y filtrar atributos
Siguiendo con el uso de los atributos, veamos varias funciones que nos permitirá realizar varios procesos con los
atributos, entre definir filtros, obtener atributos por resultados parciales o por nombre.
● Con este método whereStartsWith() podemos obtener los atributos que comienzan con un patrón.
● Con este método whereDoesntStartWith() podemos obtener los atributos que no comienzan con un
patrón.
● Este método has() retorna un boolean si existe el atributo.
● Este método get() permite obtener el detalle de un atributo.
● Este método filter() permite aplicar filtros sobre los atributos.
{{ $attributes->filter(fn (string $value, string $key) => $key == 'foo') }}
Algunos ejemplos:
{{ $attributes->whereStartsWith('wire:model') }}
{{ $attributes->whereDoesntStartWith('wire:model') }}
@if ($attributes->has('class'))
<div>Class attribute is present</div>
@endif
@if ($attributes->has(['name', 'class']))
<div>All of the attributes are present</div>
@endif
@if ($attributes->hasAny(['href', ':href', 'v-bind:href']))
<div>One of the attributes is present</div>
@endif
{{ $attributes->get('class') }}
También podemos obtener el primer resultado de una lista de atributos con:
{{ $attributes->whereStartsWith('wire:model')->first() }}
Función de flecha en PHP
Como tip adicional, las funciones de flecha en PHP lucen de la siguiente manera:
fn (argument_list) => expr
Esta sintaxis es la que usaremos al momento de crear el filtro mediante la función de filter(); a continuación,
mostramos una implementación de las funciones anteriores:
resources\views\components\alert.blade.php
filter: {{ $attributes->filter(fn (string $value, string $key) => $key == 'data-id') }}
190
Usando el componente de:
<x-alert type="error" :message="$post->title" class="mb-4" data-id='1'
data-priority='medium' />
Y tendremos como salida:
filter: data-id="1"
El equivalente a la función de flecha empleando una función clásica, seria:
{{ $attributes->filter(function (string $value, string $key) {return $key == 'data-id';})
}}
Componentes dinámicos
En muchas ocasiones resulta útil poder usar componentes de manera dinámica, indicando solo el nombre y sus
atributos; para ello, usamos el componente llamado dynamic-component, con el atributo componente que
recibe como parámetro el nombre del componente; veamos algunos ejemplos:
<x-dynamic-component component="alert" :message="$post->title" class="mt-4" />
<x-dynamic-component component="blog.show" :post="$post" class="mt-4" />
El nombre puede ser manejado también de manera dinámica mediante funciones o variables:
<x-dynamic-component :component="$alert" :message="$post->title" class="mt-4" />
Que sería la forma más útil de emplear los componentes dinámicos ya que, al cambiar el valor de la variable (en
el ejemplo anterior, la llamada $alert) se carga el componente correspondiente.
Ocultar atributos/métodos
Si desea evitar que algunos métodos o propiedades públicos queden expuestos como variables en la plantilla del
componente, puede agregarlos a una propiedad de $except del componente:
<?php
namespace App\View\Components;
use Illuminate\View\Component;
class Alert extends Component
{
protected $except = ['type'];
public function __construct(
191
public string $type,
) {}
}
Otra forma de evitar exponer las propiedades o métodos, es definiendo los mismos como protegidos o privados:
public function __construct(protected/private Post $post)
{
}
protected/private function changeTitle()
{
$this->post->title = 'New Title';
}
Asociar un componente a una ruta
También podemos asociar métodos de un componente a una ruta, lo cual es muy útil para poder aprovechar aún
más los componentes en Laravel; de esta forma, podemos emplear formularios junto con los formularios; por
ejemplo:
app\View\Components\MyManage.php
<?php
namespace App\View\Components;
class MyManage extends Component
{
***
public function render(): View|Closure|string
{
return view('components.my-manage');
}
public function handle(Param $param){
// request('TODO')
}
}
Y creamos una ruta al método de handle():
routes\web.php
192
Route::post('role/assign/permission/{$param}',[
App\View\Components\Dashboard\MyManage::class, 'handle' ])->name('my-handle');
Y creamos un formulario:
resources\views\components\\my-manage.blade.php
<div>
*** TODO
<form action="{{ route('my-handle', $param->id) }}" method="post">
*** TODO
</form>
</div>
Puedes crear tantos métodos en el componente y asignar tantas rutas de diversos tipos como necesites.
Es importante mencionar que es importante respetar las mayusculas y minusculas en el nombre de los
componentes y sus carpetas ya que, en Linux o MacOS es sensible a las mismas; por ejemplo, si el componente
es creado mediante:
$ php artisan make:component Blog/Post/Show
El componente a referenciar debe ser:
<x-Blog.Post.Show :post="$post" />
Más información de los componentes en:
https://laravel.com/docs/master/blade#components
Código fuente del capítulo:
https://github.com/libredesarrollo/book-course-laravel-base-11/releases/tag/v0.6
193
Capítulo 12: Seeders y Factories
Los seeders o semilleros son una forma sencilla de agregar datos a tu base de datos. Es especialmente útil
durante el desarrollo en el que necesita llenar la base de datos con datos de muestra en vez de generarlos de
manera manual; en definitiva, es el mecanismo que tenemos para generar esos datos de prueba que siempre
necesitamos al inicio de la aplicación.
Generar un seeder:
Para generar un seeder, que como puedes suponer no es más que un archivo, tenemos un comando de artisan:
$ php artisan make:seeder <NombreSeeder>
Donde <NombreSeeder> es el nombre que quieras darle a tu seeder; una vez definido, lo ejecutamos de la
siguiente manera:
$ php artisan db:seed <NombreSeeder>
O ejecutamos todos los seeders que tengamos registramos en DatabaseSeeder.php:
$ php artisan db:seed
Los seeders se registran en Database\Seeders
Caso práctico
Vamos a generar un seeder para las categorías:
$ php artisan make:seeder CategorySeeder
En el método de run() tenemos que hacer las inserciones en la base de datos para generar estos datos de
prueba:
<?php
namespace Database\Seeders;
use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
194
use Illuminate\Support\Facades\DB;
class CategorySeeder extends Seeder
{
/**
* Run the database seeds.
*
* @return void
*/
public function run(): void
{
DB::statement('SET FOREIGN_KEY_CHECKS=0;');
Category::truncate();
DB::statement('SET FOREIGN_KEY_CHECKS=1;');
for ($i = 0; $i < 20; $i++) {
Category::create(
[
'title' => "Category $i",
'slug' => "category-$i"
]
);
}
}
}
Explicación del código anterior
Lo primero que hacemos es apagar el check para evitar errores con las relaciones foráneas y truncar la tabla, lo
que significa, que estamos eliminando todos los registros; este paso es opcional y puedes removerlo si no te
interesa este comportamiento.
Luego, en el for, creamos los datos de prueba; luego, se puede registrar en la clase de DatabaseSeeder.php:
public function run(): void {
$this->call(CategorySeeder::class);
}
Para ejecutarlo, tenemos:
$ php artisan db:seed
O, indicando el nombre del seeder:
$ php artisan db:seed CategorySeeder
Y veremos:
195
Figura 12-1: Categorías generadas en la base de datos.
Para los posts, crearemos otro seeder:
$ php artisan make:seeder PostSeeder
Con el siguiente contenido:
<?php
namespace Database\Seeders;
class PostSeeder extends Seeder
{
public function run(): void
{
DB::statement('SET FOREIGN_KEY_CHECKS=0;');
Post::truncate();
DB::statement('SET FOREIGN_KEY_CHECKS=1;');
196
for ($i = 0; $i < 30; $i++) {
// $title = Str::random(20); // equivalente con el facade
$title = str()->random(20);
$c = Category::inRandomOrder()->first();
Post::create(
[
'title' => $title,
// 'slug' => Str::slug($title), // equivalente con el facade
'slug' => str($title)->slug(),
'content' => "<p>Lorem ipsum dolor sit amet consectetur, adipisicing elit.
Vitae aperiam culpa veritatis quasi laudantium mollitia quidem est blanditiis ullam illum
cupiditate suscipit, quia, itaque quaerat? Iure debitis laudantium aliquam maxime!</p>",
'category_id' => $c->id,
'description' => "Lorem ipsum dolor sit amet consectetur, adipisicing elit.
Vitae ",
'posted' => "yes"
]);
}
}
}
Explicación del código anterior
1. Nuevamente, truncamos los datos.
2. Mediante el método de Str::random(), generamos un string aleatorio.
3. Y con Category::inRandomOrder()->first(), obtenemos una categoría aleatoria.
Lo registra en la clase de DatabaseSeeder.php:
public function run(): void
{
$this->call(CategorySeeder::class);
$this->call(PostSeeder::class);
/* o
$this->call(
[
CategorySeeder::class,
PostSeeder::class
]);
*/
}
Para ejecutar los seeders registrados, tenemos:
$ php artisan db:seed
197
O, indicando el nombre del seeder:
$ php artisan db:seed PostSeeder
Y veremos:
Figura 12-2: Posts generados en la base de datos.
Model factories
Aunque, en el apartado anterior presentamos un mecanismo con el cual podemos generar múltiples datos de
prueba, la realidad es que, generar múltiples datos de pruebas en relaciones más complejas como pueden ser
los posts, no viene siendo la mejor opción, ya que, no tenemos un mecanismo sencillo para generar data variada
de pruebas y un esquema bien definido para generar N datos de prueba.
Los model factories proporcionan una manera fácil de definir múltiples datos que son predecibles y fáciles de
replicar; lo que lo convierte en aliado para poder desarrollar las distintas fases de una aplicación e inclusive
realizar pruebas.
Los model factories también utilizan el componente Faker el cual dispone de múltiples métodos que podemos
usar para generar datos de prueba como nombres de personas, textos, números, telefónicos y un largo etc.
198
Al momento de realizar pruebas o inicializar la base de datos de la aplicación, seguramente va a ser necesario
generar datos de prueba; Laravel permite definir un conjunto de atributos predeterminados para cada uno de sus
modelos Eloquent utilizando los model factories.
Caso práctico
Podemos mejorar la calidad de los datos de prueba para los posts y evitar registros con un mismo patrón y con
esto, tener una mejor fuente de datos al momento de programar la aplicación.
Para crear un factory, tenemos:
$ php artisan make:factory PostFactory
Con esto, tenemos un factory para los posts como el siguiente:
database\factories\PostFactory.php
<?php
namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;
/**
* @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
*/
class PostFactory extends Factory
{
public function definition(): array
{
}
}
Como puedes leer en el comentario:
/**
* @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
*/
Este model factory es solamente para los posts.
Ya con esto, podemos utilizar el componente de Fake provista por en Laravel para generar datos falsos de
prueba.
Al tener actualmente pocos modelos, vamos a crear un factory para los posts, que es la que tiene la entidad que
tenemos con más campos y la relación con la de categoría:
199
database\factories\PostFactory.php
<?php
namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;
/**
* @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
*/
class PostFactory extends Factory
{
public function definition(): array
{
// $name = $this->faker->name();
$name = $this->faker->sentence;
return [
'title' => $name,
'slug' => str($name)->slug(),
'content' => $this->faker->paragraphs(20, true),
'description' => $this->faker->paragraphs(4, true),
'category_id' => $this->faker->randomElement([1, 2, 3]),
'posted' => $this->faker->randomElement(['yes', 'not']),
'image' => $this->faker->imageUrl()
];
}
}
Como puedes apreciar en el código anterior, dependiendo del tipo de columna y lo que representa, empleamos
distintos métodos y propiedades que se asemejan más con el valor devuelto, pero, lo que tienen en común todos
estos métodos y propiedades utilizados es que, todos generan valores aleatorios.
Para el título, podemos usar varias propiedades y métodos, como la de name():
$this->faker->name()
Aunque, esta devuelve son nombres de personas, lo cual no combina muy bien con los nombres de las películas,
así que, en su lugar, usamos la propiedad de sentence:
$name = $this->faker->sentence
str($name)->slug()
Y tendremos, textos como los siguientes:
200
Voluptatem tenetur quaerat enim aut sapiente consequatur.
voluptatem-tenetur-quaerat-enim-aut-sapiente-consequatur
Para los textos más largos, usamos el método de paragraphs() la cual, genera párrafos de texto aleatorios, el
método recibe dos parámetros:
1. La cantidad de párrafos.
2. True si devuelve un texto y false si devuelve un array de textos.
$this->faker->paragraphs(20, true)
$this->faker->paragraphs(4, true)
Y tendremos textos en múltiples párrafos como los siguientes:
Illum recusandae dolorem est aut est. Iste rem ab ut sequi voluptas qui in et. Molestiae non et sed.
Nisi repellendus et laudantium ipsa quis asperiores. Eius expedita eos autem suscipit excepturi totam.
***
En el caso de las relaciones como las categorías, usamos el método de randomElement() la cual recibe un array
de valores, en este caso, corresponde a los IDs de 3 categorías que existen y deben de existir:
$this->faker->randomElement([1, 2, 3])
Para el estado de publicado o no, hacemos lo mismo, pero, indicando los valores permitidos del enum:
$this->faker->randomElement(['yes', 'not'])
Para la imagen también tenemos algunos métodos, la que se utilizó en el código anterior permite generar una
imagen en formato de URL:
$this->faker->imageUrl()
Y tendremos:
https://via.placeholder.com/640x480.png/0066ff?text=nihil
Podemos usar los Fakers mediante las propiedades como hicimos antes:
$this->faker
O un método de ayuda o helper:
faker()
Puedes ver todas las funciones y propiedades disponibles del componente Faker en el siguiente archivo:
201
vendor\fakerphp\faker\src\Faker\Generator.php
Para ejecutar los model factories, en nuestro caso, el de los posts:
database\seeders\DatabaseSeeder.php
<?php
namespace Database\Seeders;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Post;
use Database\Factories\PostFactory;
use Illuminate\Database\Seeder;
class DatabaseSeeder extends Seeder
{
/**
* Seed the application's database.
*/
public function run(): void
{
***
Post::factory(30)->create();
}
}
Como puede ver, mediante el método de factory indicamos la cantidad de registros a crear, en el ejemplo anterior,
serían 30; ejecutamos el comando:
$ php artisan db:seed
Y tendremos los 30 posts generados.
Código fuente del capítulo:
https://github.com/libredesarrollo/book-course-laravel-base-11/releases/tag/v0.7
202
Capítulo 13: Rest Api
Una Rest Api no es más que una interfaz entre sistemas que usa HTTP para obtener y enviar datos o generar
operaciones sobre esos datos en varios formatos como XML y JSON.
Para crear una Rest Api, podemos emplear exactamente la misma lógica que manejamos hasta ahora; la única
diferencia es donde van a estar registradas nuestras rutas, que ya no estarían en el archivo de web.php si no en
el archivo de api.php.
Para este capítulo, vamos a crear un nuevo proyecto en Laravel, aunque, puedes emplear el mismo proyecto que
hemos empleado hasta ahora, si decides crear un nuevo proyecto, debes de copiar las migraciones, request y
modelos de Post y Categoría.
A partir de Laravel 11, el archivo de api.php no se encuentra publicado, para publicarlo, debemos de ejecutar los
comandos de artisan:
$ php artisan install:api
El archivo api.php contiene las rutas para la creación de una Api Rest; estas rutas están diseñadas para no tener
estado, por lo que las solicitudes que ingresan a la aplicación a través de estas rutas deben autenticarse
mediante tokens y no tendrán acceso al estado de la sesión.
Con esto, se publicará el archivo de api.php:
routes\api.php
Y se instalará Sanctum en el proceso que es un paquete para habilitar la autenticación que trataremos más
adelante:
Installing dependencies from lock file (including require-dev)
Package operations: 1 install, 0 updates, 0 removals
- Downloading laravel/sanctum (vX.X)
- Installing laravel/sanctum (vX.X): Extracting archive
Ya con esto, para acceder a las rutas, debemos de colocar la URL del dominio seguido del prefijo de api:
<DOMAIN>/api/<RESOURCE>
Por ejemplo:
http://larafirststeps.test/api/category
Si quieres personalizar las rutas para indicar otro prefijo que no sea el de API:
use Illuminate\Support\Facades\Route;
203
->withRouting(
web: __DIR__.'/../routes/web.php',
commands: __DIR__.'/../routes/console.php',
health: '/up',
then: function () {
Route::middleware('api')
->prefix('webhooks')
->name('webhooks.')
->group(base_path('routes/webhooks.php'));
},
)
Más información en:
https://laravel.com/docs/master/routing#routing-customization
Recuerda ejecutar las migraciones en caso de que existan:
$ php artisan migrate
Caso práctico
Finalmente, vamos a pasar a la práctica; vamos a crear las rutas en:
routes/api.php:
Route::resource('category',
App\Http\Controllers\Api\CategoryController::class)->except(["create", "edit"]);
Route::resource('post', App\Http\Controllers\Api\PostController::class)->except(["create",
"edit"]);
Excluyendo los procesos intermedios para pintar el formulario que no serían necesarios para una Api Rest.
Controladores
Creamos los controladores para las APIs:
$ php artisan make:controller Api/PostController -m Post
Y
$ php artisan make:controller Api/CategoryController -m Category
Los cuales, tendrán la siguiente estructura.
Para el de:
204
Api/CategoryController.php
<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Requests\Category\PutRequest;
use App\Http\Requests\Category\StoreRequest;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
class CategoryController extends Controller
{
public function index(): JsonResponse
{
return response()->json(Category::paginate(10));
}
public function store(StoreRequest $request): JsonResponse
{
return response()->json(Category::create($request->validated()));
}
public function update(PutRequest $request, Category $category): JsonResponse
{
$category->update($request->validated());
return response()->json($category);
}
public function destroy(Category $category): JsonResponse
{
$category->delete();
return response()->json("ok");
}
}
Y para el de:
Api/PostController.php
<?php
namespace App\Http\Controllers\Api;
205
use App\Http\Controllers\Controller;
use App\Http\Requests\Post\PutRequest;
use App\Http\Requests\Post\StoreRequest;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
class PostController extends Controller
{
public function index(): JsonResponse
{
return response()->json(Post::paginate(10));
}
public function store(StoreRequest $request): JsonResponse
{
return response()->json(Post::create($request->validated()));
}
public function show(Post $post): JsonResponse
{
return response()->json($post);
}
public function update(PutRequest $request, Post $post): JsonResponse
{
$post->update($request->validated());
return response()->json($post);
}
public function destroy(Post $post): JsonResponse
{
$post->delete();
return response()->json("ok");
}
}
Explicación del código anterior
Puedes ver que prescindimos de algunos métodos como el de los formularios de edit y create; ya que, en una Api
Rest, no es necesario estas vistas intermedias para crear los recursos, recordemos que, estas son empleadas
para pintar el formulario y nada más, y en una Rest Api, esto no sería necesario, solamente mantener los
procesos de crear y editar.
Finalmente, siempre devolvemos una respuesta en formato JSON con: response()->json().
206
La cual recibe dos parámetros:
1. Los datos.
2. El código de estado.
Para exponer la categoría con toda la información y no solo el identificador:
{
"id": 1,
"title": "Post 1",
"slug": "post-1",
"description": "test",
"content": "test",
"image": "test",
"posted": "yes",
"category_id": 1,
"created_at": null,
"updated_at": null,
"category": {
"id": 1,
"title": "cate 1 new",
"slug": "cate-1"
}
}
Podemos indicar que traiga la relación al momento de hacer la paginación:
Api/PostController.php
public function index()
{
return response()->json(Post::with('category')->paginate(10));
}
Manejar excepciones
Para manejar las excepciones, específicamente aquellas que ocurren cuando no existen los registros al momento
de la búsqueda, por ejemplo:
"message": "No query results for model [App\\Models\\Category] cate-1asas",
"exception": "Symfony\\Component\\HttpKernel\\Exception\\NotFoundHttpException",
A partir de Laravel 11, tenemos el manejo de las configuraciones globales en un solo archivo:
bootstrap\app.php
Así que, desde el método de:
207
withExceptions
Manejamos las excepciones, desde el mencionado método podemos capturar las excepciones que queremos
personalizar:
return Application::configure(basePath: dirname(__DIR__))
***
->withMiddleware(function (Middleware $middleware) {
//
})
->withExceptions(function (Exceptions $exceptions) {
$exceptions->render(function (NotFoundHttpException $e, $request) {
if($request->expectsJson()){ // or $request->wantsJson()
return response()->json('Not found',404);
}
});
})->create();
Especificamos un manejo de excepción específico para la excepción que está ocurriendo que es la de:
Symfony\\Component\\HttpKernel\\Exception\\NotFoundHttpException
Y si se espera recibir una respuesta JSON ($this->expectsJson()) el cual es el formato que vamos a emplear
desde la Api Rest; y en este caso, generamos una excepción personalizada como la que implementamos
anteriormente. Desde el archivo anterior, puedes personalizar el comportamiento de cualquier otra excepción que
consideres.
Probar la Api Rest anterior
Para las peticiones de tipo POST, PUT, PATCH o DELETE, necesitamos usar un software que nos permita
realizar este tipo de peticiones; ya que, por el navegador no sería posible; vamos a usar Postman:
https://www.postman.com/
Postman es simplemente una aplicación para probar nuestras APIs vía HTTP mediante una interfaz sencilla.
Para probar las validaciones de tipo POST y PUT/PATCH; debes de configurar la opción de body y de tipo
x-www-form-urlencoded:
Figura 13-1: Configurar el tipo de body en Postman
Y define los parámetros correspondientes:
208
Figura 13-2: Configurar formulario en Postman
Luego, recuerda cambiar según el tipo de método que estés empleando:
209
Figura 13-3: Tipo de métodos en Postman
Y configura tus headers para que acepten el formato de JSON:
Figura 13-4: Configurar application json en el header en Postman
Probar CRUD de los posts
Las peticiones de tipo GET las puedes probar tanto del navegador como de Postman; el resto de las peticiones,
para probarlas, tienes que usar Postman.
De tipo GET para el listado:
http://larafirststeps.test/api/post
{
"current_page": 1,
"data": [
{
"id": 1,
"title": "Rtjojf8FPO05kxMuw1cZ",
"slug": "rtjojf8fpo05kxmuw1cz",
"description": "Lorem ipsum dolor sit amet consectetur, adipisicing elit. Vitae ",
"content": "<p>Lorem ipsum dolor sit amet consectetur, adipisicing elit. Vitae
aperiam culpa veritatis quasi laudantium mollitia quidem est blanditiis ullam illum
cupiditate suscipit, quia, itaque quaerat? Iure debitis laudantium aliquam maxime!</p>",
"image": null,
"posted": "yes",
"created_at": "2024-03-07T16:19:39.000000Z",
"updated_at": "2024-03-07T16:19:39.000000Z",
"category_id": 14
},
***
],
"next_page_url": "http://larafirststeps.test/api/post?page=2",
"path": "http://larafirststeps.test/api/post",
"per_page": 10,
"prev_page_url": null,
210
"to": 10,
"total": 29
}
De tipo GET para el detalle:
http://larafirststeps.test/api/post/1
{
"id": 1,
"title": "Rtjojf8FPO05kxMuw1cZ",
"slug": "rtjojf8fpo05kxmuw1cz",
"description": "Lorem ipsum dolor sit amet consectetur, adipisicing elit. Vitae ",
"content": "<p>Lorem ipsum dolor sit amet consectetur, adipisicing elit. Vitae aperiam
culpa veritatis quasi laudantium mollitia quidem est blanditiis ullam illum cupiditate
suscipit, quia, itaque quaerat? Iure debitis laudantium aliquam maxime!</p>",
"image": null,
"posted": "yes",
"created_at": "2024-03-07T16:19:39.000000Z",
"updated_at": "2024-03-07T16:19:39.000000Z",
"category_id": 14
}
De tipo POST para crear con errores:
http://larafirststeps.test/api/post
211
Figura 13-5: Formulario con errores de validación
De tipo POST para crear de manera exitosa:
http://larafirststeps.test/api/post
212
Figura 13-6: Post creado
De tipo PUT/PATCH para actualizar de manera exitosa:
http://larafirststeps.test/api/post/1
213
Figura 13-7: Actualizar un post
De tipo PUT/PATCH para actualizar con errores:
http://larafirststeps.test/api/post/1
214
Figura 13-8: Actualizar un post, errores de validación
De tipo DELETE para eliminar:
http://larafirststeps.test/api/post/1
215
Figura 13-9: Eliminar un post
Para las categorías, viene siendo exactamente lo mismo, pero empleando la URL de:
http://larafirststeps.test/api/category/<parámetros>
Y los mismos casos vistos anteriormente.
Implementar métodos personalizados
En este apartado, vamos a crear algunos métodos específicos para el consumo de los posts o categorías.
Obtenerlas todas
Ahora, vamos a crear un par de métodos para obtener todos los registros sin paginación:
216
app\Http\Controllers\Api\PostController.php
public function all(): JsonResponse
{
return response()->json(Post::get());
}
Y
app\Http\Controllers\Api\CategoryController.php
public function all(): JsonResponse
{
return response()->json(Category::get());
}
Las rutas:
routes\api.php
Route::get('post/all', [PostController::class, 'all']);
Route::get('category/all', [CategoryController::class, 'all']);
Consumir por el slug
Para consumir por el slug, podemos crear un método como los siguientes:
app\Http\Controllers\Api\PostController.php
public function slug($slug): JsonResponse
{
$post = Post::where("slug", $slug)->firstOrFail();
return response()->json($post);
}
Y
app\Http\Controllers\Api\CategoryController.php
public function slug($slug): JsonResponse
{
$category = Category::where("slug", $slug)->firstOrFail();
return response()->json($category);
}
Y para las URL, algo como las siguientes:
217
routes\api.php
Route::get('post/slug/{slug}', [PostController::class, 'slug']);
Route::get('category/slug/{category:slug}', [CategoryController::class, 'slug']);
Puedes variar la URL, pero es importante que no cause conflicto con otra ya existente, por ejemplo, la de show.
Si consumimos el método anterior, tendremos algo como lo siguiente:
// http://larafirststeps.test/api/post/slug/xgyxsfyabgyefiaubhog
{
"id": 1,
"title": "xGYxsFYABgyEFiAuBhOg",
"slug": "xgyxsfyabgyefiaubhog",
"description": "Lorem ipsum dolor sit amet consectetur, adipisicing elit. Vitae ",
"content": "<p>Lorem ipsum dolor sit amet consectetur, adipisicing elit. Vitae aperiam
culpa veritatis quasi laudantium mollitia quidem est blanditiis ullam illum cupiditate
suscipit, quia, itaque quaerat? Iure debitis laudantium aliquam maxime!</p>",
"image": null,
"posted": "yes",
"created_at": "2024-03-14T18:20:14.000000Z",
"updated_at": "2024-03-14T18:20:14.000000Z",
"category_id": 11,
"category": {
"id": 11,
"title": "Categoria 10",
"slug": "categoria-10",
"created_at": "2024-03-14T18:20:14.000000Z",
"updated_at": "2024-03-14T18:20:14.000000Z"
}
}
Si quieres que traiga la categoría asociada, puedes usar el esquema de:
$post = Post::with("category")->where("slug", $slug)->firstOrFail();
O
$post = Post::where("slug", $slug)->firstOrFail();
$post->category;
Importante notar el segundo caso, Laravel trabaja con un esquema lazy loading, lo que significa, es que, no va a
traer los datos de relaciones al menos que los solicites; en el segundo caso, estamos consumiendo la categoría
del post seleccionado y por ende, realiza la consulta a la base de datos y queda registrado en el objeto de post.
218
El método firstOrFail() trae un único registro según la condición (al igual que el método de firts()), si no lo
encuentra, entonces da un error 404.
Otra variación para el caso anterior, es definir el método de la siguiente manera:
public function slug(Post $post): JsonResponse // $slug
{
//$post = Post::with("category")->where("slug", $slug)->firstOrFail();
$post->category;
return response()->json($post);
}
Importante notar que, ahora tenemos el post inyectado en el método (es decir, como parámetro, a esto se le
conoce como inyección de dependencia) por lo tanto, para indicar a Laravel que lo que va a recibir es el slug y
que haga el mapeo al post; esto, lo indicamos por las rutas:
Route::get('post/slug/{post:slug}', [PostController::class, 'slug']);
Para las categorías, vamos a realizar el mismo procedimiento:
public function slug(Category $category): JsonResponse
{
return response()->json($category);
}
Y la ruta:
Route::get('category/slug/{category:slug}', [CategoryController::class, 'slug']);
Código fuente de la sección:
https://github.com/libredesarrollo/book-course-laravel-base-api-11/releases/tag/v0.1
219
Autenticación para la Rest Api
La autenticación en la Rest Api puede que sea una necesidad para ti; querer proteger ciertos recursos de una
Rest Api, para evitar un acceso público, son necesidades comunes; pero, no podemos o no debemos emplear el
esquema común en base a sesiones; que, aunque pudieras activarla, esto está a desfavor de las buenas
prácticas que se recomiendan al momento de crear este tipo de APIs que deben ser sin estado, entiéndase, sin
sesión.
Por lo tanto, existen otros métodos para lograr el objetivo de dotar a la Rest Api de autenticación requerida en
alguno de sus métodos o en todos que vienen siendo o basadas con cookies o por tokens.
Laravel Sanctum proporciona un sistema de autenticación para nuestra Api Rest; tenemos opciones según
nuestras necesidades; autenticación para webs de tipo SPA (aplicaciones de una sola página) y API simples
basadas en tokens.
Aparte de estas, también ofrece un tercer escenario que sería para las aplicaciones móviles que no trataremos
en este libro.
Sanctum permite que cada usuario de la aplicación genere múltiples tokens de API para su cuenta. A estos
tokens se les pueden otorgar habilidades o permisos que especifican qué acciones pueden realizar los tokens.
Laravel Sanctum permite resolver el mismo problema de ofrecer protección a una Rest Api de dos formas; que
veremos en cada uno de los apartados que presentamos a continuación.
Importante notar que, Laravel Sanctum se instala automáticamente al exponer las rutas del api mediante:
$ php artisan install:api
Autenticación para una web SPA
Sanctum ofrece una forma sencilla de autenticar aplicaciones de una sola página (SPA) que necesitan
comunicarse con una API potenciada por Laravel. Estos SPAs pueden existir en el mismo proyecto que su
aplicación Laravel o pueden ser un proyecto completamente separado, como un SPA creado con Vue CLI o una
aplicación Next.js.
Para esta función, Sanctum no utiliza fichas como tokens de ningún tipo. En su lugar, Sanctum utiliza los
servicios de autenticación de sesión basados en cookies integrados de Laravel.
Sanctum ya viene instalado en las versiones recientes de Laravel; por lo tanto, pasaremos por alto su explicación
y que puedes ver en:
https://laravel.com/docs/master/sanctum
Para poder activar la autenticación vía SPA, tenemos que realizar una sencilla configuración adicional; a nivel de
la configuración de la aplicación, tenemos que habilitar el siguiente middleware:
bootstrap/app.php
220
return Application::configure(basePath: dirname(__DIR__))
***
->withMiddleware(function (Middleware $middleware) {
$middleware->statefulApi();
})
Si vas a emplear un dominio diferente al del proyecto en Laravel, recuerda especificar el dominio que vas a
emplear en config/sanctum.php:
'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
'%s%s',
'localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1,otherdomain.test',
env('APP_URL') ? ','.parse_url(env('APP_URL'), PHP_URL_HOST) : ''
))),
Y también habilitar en el archivo de config/cors.php tienes que habilitar el supports_credentials en true.
Para poder emplear axios (que lo haremos más adelante, debes de buscar el archivo resources/js/bootstrap.js
y colocar:
axios.defaults.withCredentials = true;
axios.defaults.withXSRFToken = true;
Esto es para habilitar el uso de las cookies con las credenciales del usuario, y poder usarlo de manera
transparente con axios.
Si instalastes Laravel Breeze en el proyecto, puedes emplear la ruta:
http://larafirststeps.test/login
Para iniciar sesión y probar los recursos protegidos por Sanctum; si no lo has instalado, puedes instalarlo o
emplear Laravel Fortify:
https://laravel.com/docs/master/fortify
O un sistema de autenticación personalizado por nosotros.
Crear controlador para el login
Para evitar instalar componentes adicionales para realizar solamente el login de usuario, crearemos un
controlador para realizar el login de manera manual:
app\Http\Controllers\UserController.php
class LoginController extends Controller
{
function authenticate(Request $request) {
221
$validator = validator()->make($request->all(), ["email" =>
'required', 'email',
'password' => 'required'
]);
if($validator->fails()){
//return $validator->errors();
return response()->json($validator->errors(),422);
}
$credentials = $validator->valid();
if(Auth::attempt($credentials)){
session()->regenerate();
return response()->json('Successful authentication');
}
return response()->json('The username and/or password do not match',422);
}
}
En el método anterior, creamos un proceso para realizar validaciones:
$validator = validator()->make($request->all(), ["email" =>
'required', 'email',
'password' => 'required'
]);
Si hay errores, las retornamos:
if($validator->fails()){
return response()->json($validator->errors(),422);
}
Obtenemos los datos de autenticación del usuario (email y password):
$credentials = $validator->valid();
E intentamos realizar la autenticación mediante el método attempt() que devuelve un booleano, true si la
autenticación es exitosa, false en caso contrario; mediante el método regenerate(), se emplea para regenerar el
ID de la sesión y eliminar todos los datos de la sesión.
Ahora, creamos la ruta:
routes/api.php
222
routes/web.php
Route::post('user/login',[UserController::class, 'login']);
La ruta solamente la puedes definir dentro de api:
routes/api.php
Pero, para realizar algunas pruebas, también la colocamos dentro de web.php.
Consideración importante sobre el request
Es importante notar que la sesión es accedida mediante el método de ayuda:
session()
session()->regenerate()
Para solucionar el error, accede a la sesión desde la función de ayuda y no la petición:
$request->session()
$request->session()->regenerate()
Por defecto una Rest Api es sin estado, lo que significa que no mantiene sesion, Sanctum para la autenticación
SPA usa las cookies de sesión de Laravel y no directamente la sesión; por eso que si acceder al objeto $request
desde la API, verás que es una instancia de:
ParameterBag
A diferencia de lo que obtendremos desde el módulo web:
Illuminate\Http\Request
Lógicamente, no es lo mismo y desde la API no se puede acceder a la sesión mediante el request, así que,
cambia la forma de acceder a la sesión.
Crear usuario de prueba
Podemos crear un usuario de demostración por Breeze si tiene Breeze instalado en el proyecto o en nuestro
caso, lo hacemos mediante tinker:
$ php artisan tinker
$user = new App\Models\User();
$user->password = Hash::make('12345');
$user->email = 'admin@admin.com';
$user->name = 'Admin';
$user->save();
223
Más información en:
https://laravel.com/docs/master/authentication#authenticating-users
Pruebas con Postman, Vue y protección mediante Sanctum
En este apartado, vamos a realizar algunas pruebas con el controlador anterior para entender su funcionamiento,
recordemos que al momento de publicar las rutas del api, también tenemos una ruta protegida:
Route::get('/user', function (Request $request) {
return $request->user();
})->middleware('auth:sanctum');
A la cual vamos a intentar acceder cuando el usuario se autentique mediante el controlador anterior, desde
Postman, si intentamos autenticarnos empleando la ruta de la API, veremos un error como el siguiente:
Figura 13-10: Error en Postman al intentar autenticarse por la API
En la cual, muestra un error que indica que no es posible acceder a la sesión, esto se debe a que el uso de las
rutas del API son por defecto sin estado, cumpliendo con las buenas prácticas que indican que una Rest Api
debe ser sin estado, es decir, sin emplear la sesión.
224
Si intentamos autenticarnos mediante Postman y la ruta en web:
Figura 13-11: Error en Postman al intentar autenticarse por la web
Veremos que arroja un error de tipo 419, y esto es porque no se está suministrando el token CSRF, pudiéramos
desactivar la protección para esta ruta, aunque no es recomendable, sobre todo para este controlador tan
importante para la aplicación que es para realizar el login.
Gracias a que en axios habilitamos las opciones de withCredentials y withXSRFToken en axios, no vamos a tener
estas limitantes al momento de consumir el controlador anterior por ninguna de las rutas (api o web); para esta
prueba, lo primero que tenemos que hacer es hacer una solicitud/request a la ruta /sanctum/csrf-cookie para
inicializar la protección CSRF para la aplicación:
axios.get('/sanctum/csrf-cookie').then(response => {
// Login...
});
Luego de esta petición, podemos realizar el login en cualquier archivos JS de tu aplicación, quedando el método
como:
resources\js\blog.js
225
axios.get('/sanctum/csrf-cookie').then(response => {
this.$axios.post("/api/user/login", {
'email': 'admin@admin.com',
'password': '12345',
}).then((res) => {
console.log(res.data);
}).catch((error) => {
console.log(error);
});
Recuerda colocar en:
{
'email': 'admin@admin.com',
'password': '12345',
}
Las credenciales correctas para tu usuario.
Si definimos las credenciales correctas, desde la consola del navegador, veremos un mensaje como el siguiente:
Successful authentication
Que indica que nos autenticamos exitosamente, también puedes probar credenciales incorrectas para el usuario
y verás:
The username and/or password do not match
Lo que indica que el controlador de login funciona correctamente, puedes probar el módulo web:
$axios.post("/user/login")
Y verás que tiene un comportamiento equivalente al módulo de api.
Ahora, vamos a intentar consumir la ruta /api/user; que recordemos que se encuentra protegido por Sanctum
para los usuarios autenticados:
Route::get('/user', function (Request $request) {
return $request->user();
})->middleware('auth:sanctum');
Para ello, una vez autenticado, puedes comentar el código para realizar el login en y colocar el siguiente:
resources\js\blog.js
axios.get('/api/user').then(response => {
226
console.log(response.data)
});
Y si estás autenticado, veras la salida con las credenciales del usuario:
{id: 1, name: 'Admin', email: 'admin@admin.com', email_verified_at: null, created_at:
'2024-04-12T11:29:46.000000Z', …}
Si no estuvieras autenticado, verías un error de tipo 401, que indica que no tienes permisos para acceder al
recurso:
GET http://larafirststeps.test/api/user 401 (Unauthorized)
Proteger rutas mediante autenticación requerida
Finalmente, ya con las implementaciones anteriores y escogida algún método de autenticación (Breeze, Fortify o
personalizado), podemos emplear el middleware para la autenticación de las rutas; de manera ejemplificada,
protegeremos un conjunto de rutas:
routes\api.php
Route::group(['middleware' => 'auth:sanctum'], function () {
Route::resource('category', CategoryController::class)->except(["create", "edit"]);
Route::resource('post', PostController::class)->except(["create", "edit"]);
});
Route::get('post/all', [PostController::class, 'all']);
Route::get('post/slug/{post:slug}', [PostController::class, 'slug']);
Route::get('category/all', [CategoryController::class, 'all']);
Route::get('category/slug/{slug}', [CategoryController::class, 'slug']);
Route::get('category/{category}/posts', [CategoryController::class, 'posts']);
Para probar esta autenticación, puedes realizar peticiones vía axios desde tu aplicación a estos recursos
protegidos; para hacer estas pruebas, tienes que probar desde la aplicación, en alguna página que tengas
configurado el:
<script src="http://localhost/js/app.js" defer></script>
Por ejemplo, la página del dashboard:
http://larafirststeps.test/dashboard/post
Estando el usuario autenticado:
227
Figura 13-12: Usuario autenticado por las cookies
Sin estar autenticado:
Figura 13-13: Usuario no autenticado por las cookies
228
En definitiva, esta son algunas pruebas que podemos realizar al no tener una web SPA disponible; es importante
señalar que, podemos crear otro recurso rest para la autenticación que pudieras emplear para tu web SPA y no
emplear la provista por Breeze.
Autenticación en base a tokens
La siguiente forma que tenemos de trabajar con Sanctum, es usar un sistema de tokens.
Esta función está inspirada en GitHub y otras aplicaciones que emiten "tokens de acceso personal".
Por ejemplo, imagine que la configuración de la cuenta de su aplicación tiene una pantalla en la que un usuario
puede generar un token API para su cuenta. Puede usar Sanctum para generar y administrar esos tokens. Estos
tokens suelen tener un tiempo de caducidad muy largo (años), pero el usuario puede revocarlos manualmente en
cualquier momento.
En definitiva, podemos generar y remover tokens a usuarios, y cada token puede tener privilegios sobre
determinadas acciones que pueda realizar.
Laravel Sanctum ofrece esta función almacenando tokens de API de usuario en una sola tabla de base de datos
y autenticando las solicitudes HTTP entrantes a través del encabezado de autorización que debe contener un
token de API válido.
Antes de comenzar, vamos a deshabilitar el esquema anterior de Sanctum para la autenticación vía SPA ya que
no sería necesaria, aunque, puedes dejarla activada si es lo que prefieres:
bootstrap\app.php
->withMiddleware(function (Middleware $middleware) {
// $middleware->statefulApi();
})
Crear tokens
Para crear los tokens para nuestros usuarios, vamos a crear una función de login que permite generar estos
tokens (esta función también podrías emplear en la autenticación vía SPA pero sin generar el token):
$ php artisan make:controller Api/UserController
En el cual, definiremos el siguiente contenido:
app\Http\Controllers\Api\UserController.php
<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
229
use Illuminate\Http\Request;
class UserController extends Controller
{
function login(Request $request) {
$validator = validator()->make($request->all(),
[
'email' => 'required', 'email',
'password' => 'required'
]
);
if($validator->fails()){
return response()->json($validator->errors(),422);
}
$credentials = $validator->valid();
if(auth()->attempt($credentials)){
$token = auth()->user()->createToken('myapptoken')->plainTextToken;
return response()->json($token);
}
return response()->json('The username and/or password do not match', 422);
}
}
Explicación del código anterior
Importante notar que, podemos hacer uso del método de createToken() en cualquier instancia del usuario, en
este caso, lo hacemos en base al usuario autenticado; esto es gracias a que en el modelo de usuario tenemos el
manejo de tokens habilitado:
app\Models\User.php
use Laravel\Sanctum\HasApiTokens;
class User extends Authenticatable
{
use HasApiTokens, HasFactory, Notifiable;
***
Por lo demás, usamos el método de auth()->attempt() para comprobar credenciales y devolver una instancia del
usuario (modelos de usuario), y si son correctas generamos el tokens y lo mandamos como respuesta.
Creamos la ruta para el controlador anterior:
230
routes/api.php
Route::post('user/login',[UserController::class, 'login']);
Y probamos mediante Postman; probamos una autenticación válida y generamos el token:
Figura 13-14: Autenticarse y generar token de auth mediante Postman
Lo establecemos al momento de solicitar datos en uno de los recursos protegidos:
231
Figura 13-15: Consumir un recurso protegido con el token de autenticación
Si no pasas el token, o es incorrecto, se devuelve un error con una respuesta de tipo 422:
Figura 13-16: Consumir un recurso protegido sin el token de autenticación
Este comportamiento lo podemos cambiar; para eso, agregamos un parámetro para los headers:
Figura 13-17: Pasar el token de autenticación directamente desde la cabecera
232
Otra forma de probar el token, puedes crear el siguiente script, en el cual, establecemos el token del usuario en el
header y lo pasamos al momento de realizar la petición:
const config = {
headers: { Authorization: `Bearer TOKEN` },
};
axios.get('/api/user', config).then(response => {
console.log(response.data)
})
La autenticación con tokens, va un poco más allá, ya que, un usuario puede tener más de un token asignado y
cada token, puede tener permisos; en este libro, enseñamos el esquema clásico que es la generación de un
único token, pero si quieres obtener más información, puedes revisar la documentación oficial.
233
Capítulo 14: Consumir Rest Api desde Vue 3
En este capítulo, vamos a consumir la Rest Api anterior vía una aplicación en Vue 3, una aplicación que va a
formar parte de la misma aplicación en Laravel y, por lo tanto, no necesitamos emplear Vue CLI o generar otro
proyecto si no, instalar Vue 3 como una dependencia más mediante la NPM directamente en el proyecto en
Laravel.
Importante mencionar que, si no tienes bases sobre Vue 3, dispongo de un curso gratuitos en la plataforma de
Academia en el siguiente enlace:
https://academy.desarrollolibre.net/primeros-pasos-con-vue
https://www.desarrollolibre.net/libros/primeros-pasos-con-vue
En el cual puedes dar los primeros pasos con esta tecnología.
Agregar Vue 3 al proyecto en Laravel
Estos pasos los tienes que seguir si estás desarrollando un proyecto en Laravel que cuente con el
archivo de vite.config.js.
Vamos a agregar las dependencias de Vue 3 en el proyecto en Laravel:
$ npm install vue
$ npm install vue-loader
El vue es el paquete actual para instalar vue 3, y el vue-loader no es más que el paquete que permite procesar
los archivos .vue y generar un archivo de salida que pueda entender el navegador.
vue-loader es el loader para webpack que permite procesar los componentes de Vue.
Adicional a los paquetes anteriores, tenemos que instalar el plugin de Vue con Vite:
$ npm install @vitejs/plugin-vue
Y en el archivo de configuración de Vite, agregamos a Vue:
vite.config.js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue'
export default defineConfig({
plugins: [
vue(),
234
laravel({
input: [
'resources/css/app.css',
'resources/js/app.js',
],
refresh: true,
}),
],
});
Ahora, ya tenemos las dependencias listas, vamos a configurar el proyecto; para esto, vamos a crear los archivos
necesarios, son básicamente los mismos que existen en un proyecto generado con la Vue Cli.
Crearemos unos archivos en la carpeta resources cuya estructura y funcionalidad señalamos a continuación.
Este primer archivo seria para construir la instancia principal de Vue; recuerda que es la que también usamos
para configurar cualquier otro plugin.
resources/js/vue/main.js:
import { createApp } from "vue";
import App from "./App.vue"
const app = createApp(App)
app.mount("#app")
Crear el Proyecto en Vue
Independientemente de si estás usando Laravel mix o Vite, debes de aplicar los siguientes pasos.
Creamos el componente principal, que es útil para cargar elementos globales para el resto de los componentes;
en el mismo, vamos a cargar el componente de listado:
resources/js/vue/App.vue
<template>
<div>
<h1>App</h1>
<list/>
</div>
</template>
<script>
import List from "./components/ListComponent.vue";
export default {
components:{
235
List
}
}
</script>
Finalmente, creamos el componente de listado:
resources/js/vue/components/ListComponent.vue:
<template>
<div>
<h1>Post List</h1>
</div>
</template>
Y es en este archivo que es donde se generan los archivos finales que son los que pueden ser consumidos por el
navegador y son los que colocamos como referencia en los archivos de vistas.
Importante también notar el uso de la función .vue(), la cual es la que se encarga de traducir los archivos .vue y
generar los archivos de salidas; esta función la podemos emplear gracias al vue-loader que instalamos
anteriormente.
Ya casi estamos, ahora necesitamos una vista o archivo blade en la cual pondremos el archivo JavaScript
generado por vite usando la directiva de @vite():
resources/views/vue.blade.php
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="ie=edge">
<title>Vue</title>
</head>
<body>
<div id="app"></div>
@vite(['resources/js/vue/main.js'])
</body>
</html>
La ruta en la cual vamos a usar para la app:
routes\web.php
Route::get('/vue', function () {
return view('vue');
236
});
Si ejecutamos un:
$ npm run dev
Veremos que ahora existen tres archivos de salida; los dos anteriores, y el main.js adicional, que es nuestro Vue
que incluimos en la página blade anterior.
Finalmente, si vamos a nuestro navegador, veremos:
Figura 14-1: Hola mundo en Vue
Importante notar que, si no ves la pantalla anterior, probablemente tengas un error, debes de revisar si se generó
el archivo main.js en la carpeta public, si lo estás incluyendo correctamente o tienes algún error se sintaxis o
algún problema derivado.
Pero con los pasos anteriores, incluimos una aplicación en Vue, en el proyecto en Laravel; importante señalar
que, siempre cuando estés desarrollando la aplicación en Vue, debes de tener activo el watch o el dev en vite:
npm run dev
Para que el servidor de desarrollo detecte estos cambios y los puedas visualizar en el navegador; si vas a instalar
una dependencia adicional por Node, baja el servidor de desarrollo, instala la dependencia y ejecuta el servidor
de desarrollo nuevamente.
Configurar proyecto en Vue 3 con Oruga UI
Ahora vamos a configurar una librería para trabajar con los elementos de UI; en este caso vamos a emplear
Oruga UI:
237
Oruga es una biblioteca liviana de componentes de interfaz de usuario para Vue.js sin dependencia de CSS.
No depende de ningún estilo específico o framework CSS (como Bootstrap, Bulma, TailwindCSS, etc.) y no
proporciona ningún sistema de cuadrícula o utilidad CSS, solo ofrece un conjunto de componentes fáciles de
personalizar; por lo tanto, si quieres usar un estilo personalizado, puedes crear hojas de estilo personalizadas, o
usar el opcional que te ofrece Oruga UI o integrar un framework CSS.
En pocas palabras, Oruga nos trae un conjunto de componentes de UI que podemos emplear de manera gratuita
como botones, tablas, switch, loading y un CSS básico opcional.
Puede ser la lista completa en la documentación oficial en:
https://oruga.io/documentation/
Por lo tanto, pudieras emplear otros frameworks CSS junto a Oruga UI como Bootstrap, Tailwind.css, Bulma, etc.
Aunque, de manera opcional, podemos emplear un CSS básico de Oruga UI, que es el que vamos a usar en este
libro.
https://oruga.io/
Instalamos Oruga UI con:
npm install @oruga-ui/oruga-next
Oruga por defecto no trae una hoja de estilo aplicada, pero, tenemos una que provee de manera opcional y que
usaremos en el proyecto; para ello, debemos de instalarla con:
$ npm install @oruga-ui/theme-oruga
Configuramos el main.js agregando Oruga UI como un plugin más y el CSS adicional que vamos a usar:
resources/js/vue/main.js
import { createApp } from "vue";
import Oruga from '@oruga-ui/oruga-next'
import '@oruga-ui/theme-oruga/dist/oruga.css'
import App from "./App.vue"
const app = createApp(App).use(Oruga)
app.mount("#app")
Ya en este punto te invito a que pruebas algunos componentes de Oruga; puedes incluirlos en tu
ListComponent.vue:
238
Figura 14-2: Algunos componentes de Oruga UI
El código de prueba sería algo como:
resources/js/vue/componets/ListComponent.vue
<template>
<div>
<h1>Post List</h1>
<o-field label="Email" variant="danger" message="This email is invalid">
<o-input type="email" value="john@" maxlength="30"> </o-input>
</o-field>
<o-button @click="clickMe">Click Me</o-button>
</div>
</template>
<script>
export default {
methods: {
clickMe() {
alert("Clicked!");
},
},
};
</script>
Te invito a que comentes los CSS antes presentados, y veas el comportamiento de la aplicación; verás que el
estilo se rompe y nuevamente esto es útil si quieres emplear un framework CSS adicional.
239
//import '@oruga-ui/theme-oruga/dist/oruga.css'
***
Puedes consultar la documentación oficial para conocer cómo puedes incluir clases adicionales y variar los
colores oficiales.
Los iconos también son un tema aparte y si quieres emplearlos, tienes que instalar alguno de los siguientes
paquetes disponibles:
● https://materialdesignicons.com/
● https://fontawesome.com/
Generar un listado
Ahora sí, vamos a empezar a configurar nuestra aplicación, comenzando por el componente de listado que
creamos anteriormente.
Importante deshabilitar la autenticación requerida de Sanctum de las rutas, ya que, no la vamos a usar al
momento de desarrollar este módulo:
Route::group(['middleware' => 'auth:sanctum'], function () {
// NO coloques ninguna ruta que vayamos a emplear en este apartado por aqui
});
La intención es crear un componente paginado, por lo tanto, vamos a consumir el recurso paginado para los
posts mediante axios:
this.$axios.get("/api/post").then((res) => {
this.posts = res.data.data;
console.log(this.posts);
});
Sin embargo, si intentamos emplear axios desde un componente en Vue, te va a dar un error como el siguiente:
VM409:1 Uncaught TypeError: Cannot read properties of undefined (reading 'get')
Ya que, axios no existen en este contexto.
Primero, tenemos que realizar una configuración global:
import { createApp } from "vue";
import Oruga from '@oruga-ui/oruga-next'
import '@oruga-ui/theme-oruga/dist/oruga.css'
import axios from 'axios'
import App from "./App.vue"
240
const app = createApp(App).use(Oruga)
app.config.globalProperties.$axios = axios
window.axios = axios
app.mount("#app")
En el código anterior, estamos importando el paquete de axios, que ya viene instalado por defecto en Laravel, y
lo agregamos como una propiedad global en Vue; también aprovechamos y lo agregamos en el objeto window
de JavaScript.
Ahora si, en el componente de ListComponent.vue, vamos a desarrollar el siguiente script:
resources\js\vue\componets\ListComponent.vue
<script>
export default {
data() {
return {
posts: [],
isLoading: true,
};
},
async mounted() {
this.$axios.get("/api/post").then((res) => {
this.posts = res.data.data;
console.log(this.posts);
this.isLoading = false;
});
},
};
</script>
Un par de propiedades, una para saber si estamos cargando o no la data, y la otra para almacenar nuestros
posts. Luego, la función de tipo mounted() que se ejecuta cuando se monta el componente de Vue.
Vamos a usar el componente de tabla de Oruga UI, el cual puedes definir las columnas directamente en la data:
<script>
export default {
data() {
return {
data: [
{
id: 1,
241
first_name: 'Jesse',
***
gender: 'Male'
},
***
{
id: 5,
first_name: 'Anne',
last_name: 'Lee',
***
}
],
columns: [
{
field: 'id',
label: 'ID',
width: '40',
numeric: true
},
{
field: 'first_name',
***
]
}
}
}
</script>
O por las columnas, como vamos a hacer nosotros:
resources\js\vue\componets\ListComponent.vue
<template>
<div>
<h1>Post</h1>
<o-table :loading="isLoading" :data="posts">
<o-table-column field="id" label="ID" numeric v-slot="p">
{{ p.row.id }}
</o-table-column>
<o-table-column field="title" label="Título" v-slot="p">
{{ p.row.title }}
</o-table-column>
<o-table-column field="posted" label="Posteado" v-slot="p">
{{ p.row.posted }}
</o-table-column>
242
<o-table-column field="created_at" label="Fecha" v-slot="p">
{{ p.row.created_at }}
</o-table-column>
<o-table-column field="category" label="Categoría" v-slot="p">
{{ p.row.category.title }}
</o-table-column>
</o-table>
</div>
</template>
Importante notar que, todos los componente de Oruga UI, comienzan con el prefijo de o-, en este caso el de la
tabla tiene configuraciones adicionales para indicar bordeado, striped, efecto hover, entre otros y
:loading="isLoading".
Quedando como:
Figura 14-3: Listado de Posts
Instalar Material Design Icons
Vamos a instalar una iconográfica, como mencionamos antes podemos usar Font Awesome o Material Design
Icons; vamos a emplear este último; simplemente lo instalamos:
$ npm install @mdi/font
Y lo referenciamos:
resources/js/vue/main.js
//Material Design
import "@mdi/font/css/materialdesignicons.min.css"
Y eso sería todo, ya con esto, Oruga UI detectará los iconos al momento de emplear sus componentes; por
ejemplos el de listado que ya vamos a abordar.
243
Otro punto importante, cuando generemos los archivos de salida, veremos algo como:
│ /js/app.js │
715 KiB │
│ /js/main.js │
2.24 MiB │
│ css/app.css │
32.4 KiB │
│ fonts/vendor/@mdi/materialdesignicons-webfont.eot?e044ed23c047e571c55071b6376337f9 │
1.09 MiB │
│ fonts/vendor/@mdi/materialdesignicons-webfont.ttf?5d42b4e60858731e7b6504400f7e3d8e │
1.09 MiB │
│ fonts/vendor/@mdi/materialdesignicons-webfont.woff2?606b16427a59a5a97afbe8bb5f985394 │
353 KiB │
│ fonts/vendor/@mdi/materialdesignicons-webfont.woff?5dff34d5fed607519dcbc76eaf9fc5b9
En los cuales puedes ver, las fuentes de los iconos generados.
Paginación
Usamos el recurso de paginación para mostrar los posts, con la intención de mostrar los enlaces de paginación;
la paginación con Oruga UI es muy sencilla y tiene un alto nivel de personalización; en nuestro componente de
ListComponent.vue, vamos a definir el siguiente componente luego de la tabla:
resources\js\vue\componets\ListComponent.vue
<template>
<div>
<h1>Post List</h1>
<o-table
:loading="isLoading"
:data="posts.data"
>
***
</o-table>
<br />
<o-pagination
v-if="posts.data && posts.data.length > 0"
@change="updatePage"
:total="posts.total"
v-model:current="currentPage"
:range-before="2"
244
:range-after="2"
order="centered"
size="small"
:simple="false"
:rounded="true"
:per-page="posts.per_page"
>
</o-pagination>
</div>
</template>
<script>
export default {
data() {
return {
posts: [],
isLoading: true,
currentPage:1,
};
},
methods: {
updatePage(){
setTimeout(this.listPage, 100);
},
listPage(){
this.isLoading = true;
this.$axios.get("/api/post?page="+this.currentPage).then((res) => {
this.posts = res.data;
console.log(this.posts);
this.isLoading = false;
});
}
},
async mounted() {
this.listPage()
},
};
</script>
Explicación del código anterior
Importante notar que, ya en el componente de paginación, tenemos muchos datos parámetros de personalización
que puedes consultar en la documentación oficial de Oruga UI.
245
El componente de o-pagination, como puedes revisar en la documentación oficial, crea un conjunto o de enlaces
de paginación; el componente recibe varios parámetros para personalizar el mismo; evaluemos los usados:
● El evento de change, se ejecuta cuando el usuario da un click sobre los enlaces de paginación, el evento
recibe un parámetro opcional que viene siendo el índice de la página.
● En el props de total, indicamos el total de registros.
● Con el v-model, definimos la página actual, este parámetro va a cambiar cada vez que el usuario le dé un
click a uno de los enlaces de paginación y se actualizará con dicho índice; por lo tanto, como referencia a
la página actual, puedes emplear este v-model, o el parámetro del evento change.
● Luego definimos los rangos de cuantos enlaces quieres mostrar para los enlaces antes y después de la
página seleccionada (range-before="2" y range-after="2").
● Definimos la alineación y el tamaño del componente de paginación (order="centered" y size="small").
● Definimos el diseño completo y redondeado (simple="false" y rounded="true").
● Finalmente, define cuantos elementos quieres mostrar por página (per-page).
También puedes ver que actualizamos las referencias de:
listPage(){
***
this.posts = res.data;
***
}
Para obtener todos los datos suministrados por la paginación en Laravel como lo son:
posts.per_page
posts.total
Y poder definir las opciones del componente de paginación de Oruga.
Es decir, en el código anterior, es que, en la propiedad de los posts, ahora tenemos tanto la información de los
posts como de la paginación:
{
"current_page": 2,
"data": [
{
"id": 6,
"title": "Un0vOn6bwUqG9JhGPXDL",
"slug": "un0von6bwuqg9jhgpxdl",
"description": "Lorem ipsum dolor sit amet consectetur, adipisicing elit. Vitae ",
}
...
],
"first_page_url": "http://larafirststeps.test/api/post?page=1",
"from": 5,
"last_page": 8,
"last_page_url": "http://larafirststeps.test/api/post?page=8",
246
"links": [
{
"url": "http://larafirststeps.test/api/post?page=1",
"label": "&laquo; Previous",
"active": false
},
...
],
"next_page_url": "http://larafirststeps.test/api/post?page=3",
"path": "http://larafirststeps.test/api/post",
"per_page": 4,
"prev_page_url": "http://larafirststeps.test/api/post?page=1",
"to": 8,
"total": 29
}
Con esto, en el o-table, debes de definir el .data, para acceder al detalle de los posts, que es lo que queremos
trabajar en ese apartado.
Recuerda que en la documentación oficial tienes muchas más opciones para personalización.
Luego, creamos una función que es la que se encarga de actualizar los posts, cuando el usuario da click en una
de las opciones, se ejecuta el evento de change y traemos los datos paginados usando el v-model de
currentPage, que como comentamos, contiene el índice de la página actual; en este apartado fijate que
empleamos una función de intermediario, llamado updatePage(), que luego llama a la función que trae los posts
paginados mediante la función de setTimeout(), y esto se debe a que en la versión que se empleó de Oruga UI,
existe un retraso al momento de actualizar el valor de currentPage.
Finalmente, la función de listPage() pasa el parámetro page para saber qué página va a solicitar al servidor;
dicha función también se llama al momento de montar el componente:
async mounted() {
this.listPage()
},
Y visualmente, queda como:
Figura 14-4: Componente de paginación en Oruga
247
Ruteo con Vue Router
Vamos a necesitar crear más páginas o componentes para nuestra aplicación, en próximo sería el componente
de formulario, para crear y actualizar posts, por lo tanto, vamos a necesitar emplear más de una página; por tal
motivo, necesitamos usar un plugin que permita dicha característica, que sería el de Vue Router; que nos permite
atar los componentes a una ruta que definamos.
Instalación
Instalamos Vue Router en su última versión con:
$ npm install vue-router@4
Definir rutas
Definimos en un archivo aparte las rutas que vamos a usar; en este caso un componente llamado
SaveComponent.vue que todavía no existe y que ya vamos a crear en el siguiente apartado; pero, para que la
aplicación no de un error, por referenciar un componente que no existe, coloca la referencia al único componente
que sí existe, el de ListComponent.vue:
resources/js/vue/router.js
import { createRouter, createWebHistory } from "vue-router";
import List from './componets/ListComponent.vue'
import Save from './componets/SaveComponent.vue'
const routes = [
{
name: 'list',
path: '/vue',
component: List
},
{
name: 'save',
path: '/vue/save',
component: Save
},
]
const router = createRouter({
history: createWebHistory(),
routes:routes
})
export default router
248
Como puedes ver, cargamos un par de funciones, una para crear el componente de rutas, llamada
createRouter() que recibe como parámetros:
1. El tipo de modo histórico; el recomendado createWebHistory() que hace que el ruteo se vea normal en la
URL, pero, también puedes usar el de en base a hash con createWebHashHistory().
2. Las rutas.
La definición de las rutas que usamos pasa por definir:
● El nombre del componente (name).
● El path, para indicar la URI (path).
● El componente de Vue (component).
Componente para el renderizado de los componentes
Ahora, necesitamos definir en el componente padre, el componente de router-view que es el nombre del
componente que va a renderizar el componente cuando haga match entre la URL al momento de navegar y la
URI definida en el router.js
resources/js/vue/App.vue
<template>
<div>
<router-view></router-view>
</div>
</template>
Establecer las rutas
Ahora, tenemos que consumir el módulo con nuestras rutas que creamos anteriormente, para esto, lo hacemos
como cuando usamos cualquier plugin para Vue; tal cual hicimos con Oruga UI, cargando el componente y
hacemos uso de use().
resources/js/vue/main.js
import router from "./router"
const app = createApp(App).use(Oruga).use(router)
Crear enlaces
Para poder navegar entre cada uno de nuestros componentes, tenemos que usar el componente de RouterLink;
con la URI al componente que queremos navegar, o el nombre, ya que, estamos usando rutas con nombre, las
usamos:
resources/js/vue/components/ListComponent.vue
<template>
<div>
<h1>Post List</h1>
249
<router-link :to="{name:'save'}">Create</router-link>
<o-table ***>
***
<o-table-column field="slug" label="Actions" v-slot="p">
<router-link :to="{name:'save', params:{ 'slug': p.row.slug}}">Edit</router-link>
</o-table-column>
</o-table>
***
Importante notar que, creamos en la tabla, una columna para las opciones; de momento solamente colocamos un
enlace que apunta al componente de SaveComponent.vue (que aún no hemos creado), pasando un parámetro
del slug, para que sepamos que post queremos editar.
Componente para crear y editar post
Vamos a crear el componente para guardar publicaciones desde la aplicación en Vue 3; para eso, vamos a crear
el componente de SaveComponent.vue:
resources/js/vue/components/SaveComponent.vue
Ya con esto, recuerda actualizar las referencias en:
resources/js/vue/router.js
import { createRouter, createWebHistory } from "vue-router"
import List from './componets/ListComponent.vue'
import Save from './componets/SaveComponent.vue'
///***
Ahora, vamos a usar componentes de formularios, en Oruga UI contiene una serie de componentes de
formularios como:
● o-field para definir los agrupados de los campos.
● o-input para los campos de tipo texto.
● o-input de tipo type="textarea" para los campos de tipo textarea.
● o-select para los campos de selección.
Estos por nombrar algunos, los más comunes.
Vamos a usar los mismos para armar nuestro formulario:
resources/js/vue/components/SaveComponent.vue
<template>
<div>
<o-field label="Title">
250
<o-input value=""></o-input>
</o-field>
<o-field label="Content">
<o-input value="" type="textarea"></o-input>
</o-field>
<o-field label="Description">
<o-input value="" type="textarea"></o-input>
</o-field>
<o-field label="Posted">
<o-select placeholder="Selected a option">
<option value="yes">Yes</option>
<option value="Not">No</option>
</o-select>
</o-field>
<o-field label="Category">
<o-select placeholder="Selected a option">
<option value=""></option>
</o-select>
</o-field>
<o-button variant="primary">Send</o-button>
</div>
</template>
Con esto, si vamos a:
http://larafirststeps.test/vue/save
Veremos nuestros componentes:
251
Figura 14-5: Formulario para administrar posts.
Obtener las categorías
Para usar las categorías que tenemos en la base de datos, consumimos el recurso rest correspondiente:
/api/category/all
<script>
export default {
data() {
252
return {
categories:[]
}
},
methods:{
getCategory(){
this.$axios.get('/api/category/all').then((res) => {
this.categories = res.data
})
}
}
}
</script>
Lo llamamos en el mounted():
mounted(){
this.getCategory()
},
Y lo iteramos:
<o-field label="Category">
<o-select placeholder="Selected a option">
<option value=""></option>
<option v-for="c in categories" v-bind:key="c.id" :value="c.id">{{ c.title
}}</option>
</o-select>
</o-field>
Y por supuesto, recuerda crear la propiedad para las categorías:
categories: []
Crear un post con validaciones
En este apartado, vamos a configurar el componente para que podamos crear los posts, y mostrar los errores del
servidor provistos por las validaciones.
Vamos a comenzar creando los v-model para los posts y las propiedades para manejar el mensaje de los errores
respectivamente:
form:{
title:"",
description:"",
content:"",
category_id:"",
253
posted:"",
},
errors:{
title:"",
description:"",
content:"",
category_id:"",
posted:"",
}
}
Para el proceso de crear el post (then()) y capturar errores (catch()) de formularios que es la respuesta de tipo
422:
submit(){
console.log(this.form)
this.cleanErrorsForm()
this.$axios.post("/api/post",
this.form
).then(res => {
console.log(res)
}).catch(error =>{
console.log(error.response.data)
if(error.response.data.title)
this.errors.title = error.response.data.title[0]
if(error.response.data.description)
this.errors.description = error.response.data.description[0]
if(error.response.data.category_id)
this.errors.category_id = error.response.data.category_id[0]
if(error.response.data.posted)
this.errors.posted = error.response.data.posted[0]
if(error.response.data.content)
this.errors.content = error.response.data.content[0]
})
},
254
Los errores pueden estar presentes o no, depende de lo enviado por el usuario, y es por eso los condicionales
que verifican si hay errores o no, si hay errores, entonces solamente mostramos el primero y lo establecemos en
la propiedad en cuestión.
Limpiar los errores cada vez que hacemos un submit; esto es importante para evitar mostrar el estado anterior
del formulario:
cleanErrorsForm() {
this.errors.title = ""
this.errors.description = ""
this.errors.category_id = ""
this.errors.content = ""
this.errors.posted = ""
},
Y en template para el formulario:
<form @submit.prevent="submit">
<o-field label="Título" :variant="errors.title ? 'danger' : 'primary'"
:message="errors.title">
<o-input v-model="form.title" value=""></o-input>
</o-field>
<o-field :variant="errors.description ? 'danger' : 'primary'"
:message="errors.description" label="Descripción">
<o-input v-model="form.description" type="textarea" value=""></o-input>
</o-field>
<o-field :variant="errors.content ? 'danger' : 'primary'" :message="errors.content"
label="Contenido">
<o-input v-model="form.content" type="textarea" value=""></o-input>
</o-field>
<o-field :variant="errors.category_id ? 'danger' : 'primary'"
:message="errors.category_id" label="Categoría">
<o-select v-model="form.category_id" placeholder="Seleccione una categoría">
<option v-for="c in categories" v-bind:key="c.id" :value="c.id">
{{ c.title }}
</option>
</o-select>
</o-field>
<o-field :variant="errors.posted ? 'danger' : 'primary'" :message="errors.posted"
label="Posted">
<o-select v-model="form.posted" placeholder="Seleccione un estado">
<option value="yes">Si</option>
<option value="not">No</option>
</o-select>
255
</o-field>
<o-button variant="primary" native-type="submit">Enviar</o-button>
</form>
Lo único que hacemos de diferente es, colocar los v-model correspondientes y definir el mensaje y clase para
cuando existen errores; por ejemplo:
<o-field :variant="errors.posted ? 'danger' : 'primary'" :message="errors.posted"
label="Posted">
Para eso preguntamos por la condición del mensaje de los errores para cada campo.
Con esto, tendremos un componente como el siguiente:
256
Figura 14-6: Errores en el formulario
Completamente funcional, y permite crear un post si los datos son correctos y mostrar los errores si existen
problemas con la validación de los campos en el servidor.
257
Editar un registro
Ahora que ya tenemos el proceso de crear, vamos a adaptarlo para que funcione con el proceso de edición; en el
archivo de las rutas, colocamos un parámetro opcional que corresponde al slug, el slug del post que queremos
editar, y que nos servirá para la búsqueda del detalle del post:
resources/js/vue/router.js
{
name:'save',
path:'/vue/save/:slug?',
component: Save
},
Ahora, desde el componente de SaveComponent.vue, definimos una nueva propiedad:
data() {
return {
***
post:""
};
},
En la cual registramos el post que queremos editar; cuando montamos el mencionado componente, preguntamos
si el slug está o no definido:
resources/js/vue/componets/SaveComponent.vue
async mounted() {
if(this.$route.params.slug){
await this.getPost();
this.initPost();
}
this.getCategory();
},
Recuerda que para acceder a un parámetro que va vía la URL, tenemos:
this.$route.params.<PARAMETRO>
Lógicamente si está definido, entonces estamos en el proceso de editar.
En la función de getPost(), obtenemos el detalle del post mediante el slug:
async getPost() {
this.post = await this.$axios.get("/api/post/slug/"+this.$route.params.slug);
this.post = this.post.data
258
},
En la función de initPost() inicializamos el formulario, en otras palabras, los v-model de cada una de nuestras
propiedades:
initPost(){
this.form.title = this.post.title
this.form.description = this.post.description
this.form.content = this.post.content
this.form.category_id = this.post.category_id
this.form.posted = this.post.posted
}
Y en el proceso del submit, puedes preguntar por cualquiera de las formas manejables en este componente para
saber si estamos en la fase de editar o crear para saber a qué recurso rest vamos a invocar:
submit(){
this.cleanErrorsForm()
if(this.post == "")
return this.$axios.post("/api/post",
this.form
).then(res => {
console.log(res)
}).catch(error =>{
console.log(error.response.data)
if(error.response.data.title)
this.errors.title = error.response.data.title[0]
if(error.response.data.description)
this.errors.description = error.response.data.description[0]
if(error.response.data.category_id)
this.errors.category_id = error.response.data.category_id[0]
if(error.response.data.posted)
this.errors.posted = error.response.data.posted[0]
if(error.response.data.content)
this.errors.content = error.response.data.content[0]
})
// actualizar
259
this.$axios.patch("/api/post/"+this.post.id,
this.form
).then(res => {
console.log(res)
}).catch(error =>{
console.log(error.response.data)
if(error.response.data.title)
this.errors.title = error.response.data.title[0]
if(error.response.data.description)
this.errors.description = error.response.data.description[0]
if(error.response.data.category_id)
this.errors.category_id = error.response.data.category_id[0]
if(error.response.data.posted)
this.errors.posted = error.response.data.posted[0]
if(error.response.data.content)
this.errors.content = error.response.data.content[0]
})
},
Tendrás que quitar el required del proceso de validación del slug, ya que, no le estamos pasando el slug (o crear
un campo de slug en el formulario para el slug); de:
"slug" => "required|min:5|max:500|unique:posts,slug,".$this->route("post")->id,
A:
"slug" => "required|min:5|max:500|unique:posts,slug,".$this->route("post")->id,
Eliminar un registro
El proceso de eliminar es el más sencillo al no requerir de un componente adicional; basta con emplear el de
listado y agregar dicha opción.
Vamos a crear una función que reciba un post y lo elimine:
deletePost(row) {
this.posts.data.splice(row.index,1)
// console.log(row);
this.$axios.delete("/api/post/"+row.row.id);
},
260
Importante notar que, no solamente recibimos el post, también recibimos el row, es decir, la fila entera; esto es
importante ya que, necesitamos el índice de la columna para poder eliminar el post del listado del post de nuestro
array de posts y hacer también la actualización en el listado; desde el template, creamos el botón:
<o-table-column field="slug" label="Actions" v-slot="p">
<router-link :to="{ name: 'save', params: { slug: p.row.slug } }"
>Edit</router-link
>
<o-button variant="danger" @click="deletePost(p)"
>Delete</o-button
>
</o-table-column>
Al cual pasamos el row, como comentamos antes; si analizamos el row, verás que tiene la siguiente estructura:
colindex: 5
index: 0
row: Proxy {id: 9, title: 'bpOkH3qSar3xz40HlnDqasasas', slug: 'bpokh3qsar3xz *** }
Y pensando en dicha estructura, la función de borrar el post, tiene la estructura anteriormente señalada.
Parámetros opcionales para la ruta de Vue en Laravel
Al trabajar con Vue Router; veremos a medida que vamos navegando entre los componentes de Vue; por
ejemplo, si le damos click al enlace de “Crear”, veremos que la URL cambia, y coloca el componente en cuestión:
http://larafirststeps.test/vue/save
Pero, si refrescamos las páginas, verás que Laravel nos da un 404; esto es algo bastante obvio, ya que, cuando
recargamos el navegador, estamos haciendo una petición del lado del servidor, y con esto, Laravel no sabe qué
hacer con esa ruta, ya que, la misma no existe; para evitar este comportamiento, vamos a definir la misma ruta
que usamos para el módulo de Vue de la siguiente manera:
Route::get('/vue/{n1?}/{n2?}/{n3?}', function () {
return view('vue');
});
Con estos parámetros opcionales, podemos hacer composiciones en la URL empleando Vue Router; en la
práctica, si vamos a la ruta anterior:
http://larafirststeps.test/vue/save/
Y recargamos la página, no aparece el error de 404.
Ya esta ruta, gracias al cambio que hicimos anteriormente, es reconocida por Laravel y con esto, la aplicación del
lado del cliente, entiéndase Vue, sigue funcionando sin problemas.
261
Tailwind.css en el proyecto en Vue con Oruga UI
En este apartado vamos a trabajar con el estilo, el estilo que incluye tanto emplear opciones propias de Oruga UI,
como opciones que podemos usar en los componentes de Oruga UI como colores, iconos, redondeado… Y
también incluir algún framework CSS para el resto de los detalles, como contenedores, márgenes, alineado, etc;
en pocas palabras, cuando queremos definir un estilo personalizado sobre cualquier aspecto que no podamos
cubrir con Oruga UI.
Instalamos Tailwind y el plugin para Vue:
$ npm install tailwindcss @tailwindcss/vite
Creamos un nuevo archivo que es el que vamos a usar para el estilo de la aplicación en Vue:
resources\css\vue.css
@import "tailwindcss";
Y lo importamos en el proyecto Vue:
resources\js\vue\main.js
//***
//tailwind
import '../../css/vue.css'
// Oruga
import Oruga from '@oruga-ui/oruga-next'
//***
Container
Ahora, ya con Tailwind.css configurado en nuestro proyecto en Laravel con Vue, vamos a aplicar un container
para que el contenido que tenemos no se vea tan estirado:
resources\views\vue.blade.php
<div class="container mx-auto">
<div id="app"></div>
</div>
Cambios varios en el componente de listado
En este apartado, vamos a colocar algunos iconos y márgenes para el apartado de acciones en la tabla; aparte
de esto, cambiamos el enlace en la fase de creación por un botón y hacemos la navegación de manera
programática:
resources\js\vue\componets\ListComponent.vue
262
<template>
<div>
<h1>Post List</h1>
<o-button iconLeft="plus" @click="$router.push({ name: 'save' })" >Create</o-button>
<div class="mb-5" ></div>
<o-table
:loading="isLoading"
:data="posts.current_page && posts.data.length == 0 ? [] : posts.data"
>
//***
<o-table-column field="slug" label="Acciones" v-slot="p">
<router-link class="mr-3" :to="{ name: 'save', params: { slug: p.row.slug } }"
>Edit</router-link
>
<o-button iconLeft="delete" rounded size="small" variant="danger"
@click="deletePost(p)"
>Delete</o-button
>
</o-table-column>
</o-table>
<div class="mb-5"></div>
<o-pagination
// ***
También recuerda definir tu estilo global para los H1 que usaremos para definir los títulos de cada pantalla:
resources\css\vue.css
//***
@import 'tailwindcss/utilities';
h1{
@apply text-3xl text-center my-5
}
Y queda de la siguiente manera:
263
Figura 14-7: Listado con estilo
Cambios varios en el componente de guardado
En este apartado, vamos a colocar los campos de formulario en un sistema de grid, para evitar que el contenido
se vea todo estirado:
resources\js\vue\componets\SaveComponent.vue
<template>
<h1 v-if="post">Update Post <span class="font-bold">{{post.title}}</span></h1>
<h1 v-else>Create Post </h1>
<form @submit.prevent="submit">
<div class="grid grid-cols-2 gap-3">
<div class="col-span-2">
<o-field
label="Title"
:variant="errors.title ? 'danger' : 'primary'"
:message="errors.title"
>
<o-input v-model="form.title" value=""></o-input>
</o-field>
</div>
<o-field
:variant="errors.description ? 'danger' : 'primary'"
//***
</div>
<o-button variant="primary" native-type="submit">Enviar</o-button>
</form>
264
</template>
Y queda de la siguiente manera:
Figura 14-8: Componente para guardar con estilo
Mensaje de confirmación para eliminar
Para evitar borrar registros por errores, vamos a colocar un diálogo de confirmación; que en Oruga UI ya
tenemos un componente de modal llamado o-modal; el mismo implementa un v-model:active con el cual
indicamos si queremos ver el modal (true) o no (false), por lo demás, el o-modal de Oruga UI no es más que un
contenedor en el cual colocamos cualquier HTML:
resources\js\vue\componets\ListComponent.vue
<o-modal v-model:active="confirmDeleteActive">
<div class="p-4">
<p>Are you sure you want to delete the selected record?</p>
</div>
<div class="flex flex-row-reverse gap-2 bg-gray-100 p-3">
<o-button variant="danger" @click="deletePost()">Delete</o-button>
<o-button @click="confirmDeleteActive = false">Cancel</o-button>
</div>
</o-modal>
265
<h1>Post List</h1>
***
Como puede ser, el modal que construimos consta de un mensaje de confirmación, y los botones de acción:
● Cancelar, para cerrar el modal.
● Eliminar, para borrar el registro; para esto, vamos a usar la misma función de eliminar que
implementamos anteriormente, pero removiendo el parámetro de la función que ahora debemos
referenciar desde una propiedad de Vue.
Para la acción de eliminar de la tabla, al ya no borrar el registro de manera directa, vamos a modificarlo para que
haga dos pasos:
1. Abra el modal mediante el v-model que abre al modal.
2. Establece la propiedad que vamos a usar para eliminar el registro seleccionado.
<o-table>
***
<o-button
iconLeft="delete"
rounded
size="small"
variant="danger"
@click="
deletePostRow = p;
confirmDeleteActive = true;
"
>Delete</o-button
>
</o-table-column>
</o-table>
La declaración de las nuevas propiedades para manejar la visualización del modal y para la referencia al registro
a eliminar respectivamente:
data() {
return {
//***
confirmDeleteActive: false,
deletePostRow: "",
};
Finalmente, adaptamos la función de eliminar, para eliminar el registro seleccionado:
deletePost() {
this.confirmDeleteActive = false;
this.posts.data.splice(this.deletePostRow.index, 1);
this.$axios.delete("/api/post/" + this.deletePostRow.row.id);
},
266
Y tenemos:
Figura 14-9: Modal para eliminar
Este es un diseño simple y mínimo, pero puedes adaptarlo para que tenga cabeceras, mensajes más completos,
referencia al registro que quieres eliminar o lo que consideres; también es importante notar que tenemos
problemas con el PADDING del contenedor de Oruga, por eso es que el color de fondo que colocamos para el
apartado de acciones (los botones) aparece con una separación; esto lo arreglamos en el próximo apartado.
Mensaje de acción realizada
Otro detalle que falta para nuestra aplicación, viene siendo el de mostrar un mensaje a la acción realizada; por
ejemplo, cuando creamos, actualizamos o eliminamos un registro, no existe de momento algún mensaje que
indica al usuario que dicha acción se llevó a cabo; en Oruga UI tenemos dos formas de hacer esto; mediante
notificaciones tipo bloques:
Figura 14-10: Notificación de ejemplo
O por los famosos toasts:
Figura 14-11: Notificación de ejemplo al actualizar
267
https://oruga.io/components/Notification.html#examples
En ese libro vamos a usar los últimos; los cuales, para usarlos, tenemos que usar la función de:
this.$oruga.notification.open()
E indicamos mediante un objeto, las opciones de dicha notificación; podemos personalizar bastantes aspectos
sobre la misma como colores, posiciones, mensajes, duración, efecto, entre otros; entre los principales tenemos:
● message: Para definir el mensaje, que puede ser texto o HTML.
● position: Para indicar la posición: top-right, top, top-left, bottom-right, bottom, bottom-left.
● variant: Para indicar el color.
● closable: Para indicar si quieres que pueda cerrarse con un click.
Finalmente, con esto en mente, vamos a notificar dichas acciones en el listado:
resources\js\vue\componets\ListComponent.vue
deletePost() {
this.confirmDeleteAction = false
this.$oruga.notification.open({
message: 'Delete success',
position:'bottom-right',
variant: 'danger',
duration: 4000,
closable:true
})
this.$axios.delete('/api/post/' + this.deletePostRow.row.id)
this.posts.data.splice(this.deletePostRow.index, 1)
}
}
Para actualizar y crear:
resources\js\vue\componets\SaveComponent.vue
send() {
this.cleanErrorsForm()
if (this.post == '') {
// create
this.$axios.post('/api/post', this.form).then(res => {
console.log(res)
this.$oruga.notification.open({
268
message: 'Record created success',
position:'bottom-right',
duration: 4000,
closable:true
})
}).catch(error => {
***
})
} else {
// update
this.$axios.patch('/api/post/' + this.post.id, this.form).then(res => {
this.$oruga.notification.open({
message: 'Record updated success',
position:'bottom-right',
duration: 4000,
closable:true
})
console.log(res)
}).catch(error => {
***
}
}
}
Y con esto, al realizar una de las acciones anteriormente mencionadas, tendremos un mensaje como el de la
figura 14-11.
Upload de archivos
En este apartado, vamos a conocer cómo podemos implementar la carga de archivos con Laravel, un
componente de Oruga UI y Vue 3.
Recurso Rest
Primero, vamos a implementar un recurso rest para la carga del archivo; es exactamente el mismo que usamos
desde el módulo dashboard, pero adaptado a que devuelve un JSON en lugar de una redirección y en este
método solamente procesamos la imagen cargada más no todos los datos del post:
app\Http\Controllers\Api\PostController.php
use Illuminate\Http\Request;
***
function upload(Request $request, Post $post) {
$data['image'] = $filename = time() . '.'. $request->image->extension();
269
$request->image->move(public_path('image'), $filename);
$post->update($data);
return response()->json($post);
}
Creamos la ruta:
routes\api.php
Route::post('post/upload/{post}', [PostController::class, 'upload']);
Y probamos por Postman:
Figura 14-12: Upload respuesta
Es importante colocar el campo de tipo file y subir el archivo.
Vue 3 y componente upload en Oruga UI
Ahora, vamos a usar el componente de Oruga UI para la carga de archivos:
<o-upload v-model="file">
270
***
</o-upload>
El cual, como puedes ver, define un v-model para establecer el archivo seleccionado.
Nuestro código quedará de la siguiente manera; para el template:
<template>
//***
<o-select v-model="form.posted" placeholder="Seleccione un estado">
<option value="yes">Si</option>
<option value="not">No</option>
</o-select>
</o-field>
<div class="flex gap-2" v-if="post">
<o-upload v-model="file">
<o-button tag="upload-tag" variant="primary">
<o-icon icon="upload"></o-icon>
<span>Click to upload</span>
</o-button>
</o-upload>
<o-button icon-left="upload" @click="upload">
Upload
</o-button>
</div>
</div>
<br />
//***
<o-button variant="primary" native-type="submit">Enviar</o-button>
</template>
Su propiedad:
data() {
return {
categories: [],
//***
file: null,
};
},
Y el método de upload(), la cual será una petición por axios, estableciendo la cabecera de multipart/form-data
para indicar que podemos cargar archivos:
271
methods: {
//***
upload() {
//return console.log(this.file)
const formData = new FormData()
formData.append("image",this.file)
this.$axios
.post("/api/post/upload/" + this.post.id, formData, {
headers: {
"Content-Type": "multipart/form-data",
},
})
.then((res) => {
console.log(res);
})
.catch((error) => {
console.log(error);
});
},
},
Con esto, tenemos un proceso de upload completamente funcional:
Figura 14-13: Componente de carga y botón de upload
El atributo tag establecido en el o-button anterior con el valor de upload-tag, crea un elemento con el valor del
atributo como el siguiente:
<label id="e4bxccxnjft" class="o-upl" data-oruga="upload">
<upload-tag class="o-btn o-btn--primary" role="button" data-oruga="button">
<span class="o-btn__wrapper">
<span class="o-btn__label">
<span class="o-icon" data-oruga="icon">
<i class="mdi mdi-upload mdi-24px"></i>
</span>
<span>Click to upload</span>
</span>
</span>
</upload-tag>
<input type="file" data-oruga-input="file" aria-labelledby="e4bxccxnjft">
272
</label>
Que permite emplear el botón como acción para abrir el modal de upload del sistema operativo, el atributo tag
puede tener cualquier valor, aunque se ha probado como valores como button y no aparece el diálogo de upload
del sistema operativo.
Manejo de errores de formulario
Para mostrar los errores que puedan ocurrir en el servidor (por ejemplo, un tipo de archivo no soportado), en el
controlador, vamos a colocar las validaciones locales a el método:
public function upload(Request $request, Post $post)
{
$request->validate([
'image' => "required|mimes:jpeg,png,gif|max:10240"
]);
// ***
}
Vamos a embeber el o-upload en un o-field para poder manejar el mensaje de error al igual que hicimos con los
campos de formulario anteriores:
<o-field :message="fileError">
<o-upload v-model="file">
****
</o-upload>
</o-field>
Creamos la propiedad:
fileError: "",
Y definimos en el método de upload, en el catch, el mapeo de los errores:
upload() {
//return console.log(this.file)
this.fileError = ""
const formData = new FormData();
formData.append("image", this.file);
this.$axios
.post("/api/post/upload/" + this.post.id, formData, {
headers: {
"Content-Type": "multipart/form-data",
},
})
273
.then((res) => {
console.log(res);
})
.catch((error) => {
this.fileError = error.response.data.message;
});
},
Si colocas un tipo de archivo no soportado, verás un error del formulario como lo ocurrido al momento de
procesar el post.
Si quieres que el texto aparezca en rojo, creamos el siguiente estilo:
.o-field__message{
@apply text-red-800
}
O colocas la variante:
<o-field :variant="fileError ? 'danger' : 'primary'"
Opcional: Upload de archivos vía Drag and Drop
La carga de archivos en base al Drag and Drop, arrastrando el archivo a un contenedor es una práctica muy
requerida hoy en día; vamos a conocer como podemos emplear este componente de Oruga UI.
Este tipo de carga de archivos es particularmente útil cuando queremos cargar varios archivos (carga múltiple de
archivos) que no se ajusta a nuestras necesidades ya que, recuerda que un post solamente puede tener una
única imagen; sin embargo, desde el método controlador para el upload, podemos usar sin problemas el upload
de tipo múltiple.
Definimos una propiedad de tipo array, ya que, podemos tener múltiples archivos como explicamos
anteriormente:
filesDaD: [],
En cuanto al contenedor, es similar al anterior, ya que, seguimos empleando el o-upload y definimos un par de
atributos para la carga múltiple (multiple) y habilitar el drag and drop:
<div class="flex gap-2" v-if="post">
<o-field :message="fileError">
<o-upload v-model="filesDaD" multiple drag-drop>
<section>
<o-icon icon="upload"></o-icon>
<span>Drag and Drop area</span>
</section>
</o-upload>
274
</o-field>
<span v-for="(file, index) in filesDaD" :key="index">
{{ file.name }}
</span>
</div>
Por lo demás, definimos un SECTION como elemento contenedor, pero puedes adaptar este contenedor con el
diseño que quieras.
Para ver los archivos que vamos cargando, podemos iterar los mismos que se encuentran almacenados en la
propiedad filesDaD:
<span v-for="(file, index) in filesDaD" :key="index">
{{ file.name }}
</span>
Para observar los cambios y subir un archivo al servidor cada vez que el usuario arroja uno sobre el contenedor,
vamos a usar exactamente el mismo código de la función que definimos anteriormente, pero, pasando como
parámetro del FormData la referencia al archivo en vez de la propiedad file:
watch: {
filesDaD: {
handler(val) {
//return console.log(val[val.length - 1]);
this.fileError = ""
const formData = new FormData();
formData.append("image", val[val.length - 1]);
this.$axios
.post("/api/post/upload/" + this.post.id, formData, {
headers: {
"Content-Type": "multipart/form-data",
},
})
.then((res) => {
console.log(res);
})
.catch((error) => {
this.fileError = error.response.data.message;
});
},
deep: true,
},
},
Finalmente, tendremos:
275
Figura 14-14: Caja para el Drag and Drop
Debe establecer deep en true cuando observe un array u objeto para que Vue sepa que debe observar los datos
anidados en busca de cambios.
Borrar archivos anteriores
Para evitar mantener las imágenes anteriores previas a una actualización, las podemos borrar empleando los
discos; primero, vamos a definir un disco que apunte a la carpeta public, que es la que usamos para almacenar
las imágenes:
config\filesystems.php
'disks' => [
'public_upload' => [
'driver' => 'local',
'root' => public_path()
],
***
Los discos son un concepto manejado por Laravel que representa una ubicación de almacenamiento y su
configuración, a nivel del proyecto, tenemos algunos pocos definidos que son locales, pero también pueden ser
otros como un servicio de Amazon Web Server; a la final, tenemos un conjunto de métodos para cargar, listar o
eliminar archivos.
Los discos son una interfaz que facilitan la gestión de almacenamiento y podemos modificar la implementación
de estos sin necesidad de editar los lugares en los que lo utilicemos de manera directa si no lo hacemos,
mediante Laravel.
Luego, en el método de upload(), borramos la imagen anterior antes de hacer la actualización:
public function upload(Request $request, Post $post)
{
// ***
$request->validate([
'image' => "required|mimes:jpeg,png,gif|max:10240"
]);
276
Storage::disk("public_upload")->delete("image/".$post->image);
// ***
}
Con esto, logramos el CRUD completo desde la aplicación en Vue 3 con Oruga UI de una entidad empleando la
Rest Api en Laravel.
Migrar rutas a App.vue
Como buenas prácticas para conseguir una aplicación modular, vamos a definir todas las rutas en un solo
archivo, específicamente en el componente padre, que luego podemos referenciar desde los componentes hijos:
resources\js\vue\App.vue
<script>
export default {
data() {
return {
urls: {
postUpload:'/api/post/upload/',
postPaginate:'/api/post/',
postPatch:'/api/post/',
postPost:'/api/post/',
postDelete:'/api/post/',
getPostBySlug:'/api/post/slug/',
getCategoriesAll:'/api/category/all',
}
}
},
}
</script>
Con esto, ganamos reutilizacion del codigo, tambien cuando la aplicacion va creciendo en componentes y rutas
consumidas, podemos ver en un solo lugar cuáles son las rutas que tenemos definidas en la aplicación y
administrar las mismas o realizar cualquier cambio; claro está, que para estos casos también podemos emplear
manejadores de estados como Pinia, cuyo tema es tratado en mi libro de Vue:
https://www.desarrollolibre.net/libros/primeros-pasos-con-vue
Pero, debido al alcance de la aplicación, no fue tratada y empleamos un esquema más sencillo como lo es el de
emplear una variable desde el componente padre, finalmente, hacemos los cambios desde los componentes en
Vue:
resources\js\vue\componets\SaveComponent.vue
this.$axios.get(this.$root.urls.getPostBySlug + this.$route.params.slug)
277
this.$axios.post(this.$root.urls.postUpload+this.post.id, formData, {
headers: {
'Content-Type' : 'multipart/form-data'
}
})
this.$axios.get(this.$root.urls.getCategoriesAll)
this.$axios.post(this.$root.urls.postPost, this.form)
this.$axios.patch(this.$root.urls.postPatch + this.post.id, this.form)
this.$axios.post(this.$root.urls.postUpload+this.post.id, formData, {
headers: {
'Content-Type' : 'multipart/form-data'
}
})
resources\js\vue\componets\ListComponent.vue
this.$axios.get(this.$root.urls.postPaginate+'?page=' + this.currentPage)
this.$axios.delete(this.$root.urls.postDelete + this.deletePostRow.row.id)
Código fuente del apartado:
https://github.com/libredesarrollo/book-course-laravel-base-api-11/releases/tag/v0.2
Consumir la Rest Api protegida por Sanctum vía SPA y tokens
En este capítulo, vamos a dotar a la aplicación de protección a los recursos rest mediante un token de acceso de
Laravel Sanctum que implementamos anteriormente; esto se debe a que, muchas veces queremos proteger ya
sea toda la rest api o una parte mediante un usuario autenticado que puede tener un estado o rol en particular
para acceder a ciertos recursos. Claro está, que si protegemos la rest api aquel sistema que consuma la misma
debe de proveer las credenciales pertinentes para acceder a las mismas, específicamente, la aplicación en Vue.
Login: Crear ventana
Comencemos creando el formulario que se va a usar para el login con el cual, iniciaremos la autenticación SPA
y/o por tokens; dicho formulario, consta de un par de campos de texto:
1. Para el email.
2. Para la contraseña.
Y un botón para realizar el submit:
resources\js\vue\componets\auth\LoginComponent.vue
278
<template>
<form @submit.prevent="submit">
<o-field label="Username" :variant="errors.login ? 'danger' : 'primary'"
:message="errors.login">
<o-input v-model="form.email"></o-input>
</o-field>
<o-field label="Password" :variant="errors.login ? 'danger' : 'primary'"
:message="errors.login">
<o-input v-model="form.password" type="password"></o-input>
</o-field>
<o-button variant="primary" native-type="submit">Send</o-button>
</form>
</template>
<script>
export default {
data() {
return {
form: {
email: 'admin@admin.com',
password: '12345',
},
errors: {
login: ''
},
}
},
methods: {
cleanErrorsForm(){
this.errors.login=''
},
submit() {
this.cleanErrorsForm()
axios.get('sanctum/csrf-cookie').then(response => {
axios.post('/api/user/login', this.form).then(response => {
console.log(response.data)
this.$oruga.notification.open({
message:'Login success',
279
position: 'bottom-right',
duration:1000,
closable:true
})
}).catch(error => {
this.errors.login=error.response.data
})
})
},
}
}
</script>
Registramos el componente en las rutas:
resources\js\vue\router.js
import Login from './componets/auth/LoginComponent.vue'
const routes = [
{
name: 'login',
path: '/vue/login',
component: Login
},
***
}
Y tendremos:
280
Figura 14-15: Componente de Login
Login: Obtener token
Para la petición de generar el token en base al usuario y contraseña usaremos el método de:
routes\api.php
Route::post('user/login',[UserController::class, 'login']);
resources\js\vue\componets\Auth\Login.vue
Y por lo tanto, enviamos una petición de tipo POST con los datos usando axios:
cleanErrorsForm() {
this.errors.login = "";
},
submit() {
this.cleanErrorsForm();
this.$axios
.post("/api/user/login", this.form)
.then((res) => {
console.log(res.data);
this.$oruga.notification.open({
message: "Login success",
position: "bottom-right",
duration: 1000,
closable: true,
});
})
281
.catch((error) => {
console.log(error);
if (error.response.data) {
this.errors.login = error.response.data;
}
});
},
Y con esto, tendríamos como respuesta el token de acceso, algo como:
12|dmaBjXuHuEd3HbbcebTNJLYu9rMifM1LbhAyDnB0
Es importante notar en el código anterior el manejo de los errores, ya que, el error es solamente uno, que aplica
cuando el usuario o la contraseña no son válidos o no corresponden a ningún usuario en la base de datos,
veríamos:
Figura 14-16: Login inválido
Basta con desplegar el error en solamente uno de los campos, por eso, se usa el campo de password para
mostrar el error mediante un solo objeto; a diferencia de la implementación realizada cuando administramos los
post o categorías, en las cuales, para cada campo de formulario, teníamos un campo equivalente para manejar
los errores; en definitiva, si la pareja de usuario y contraseña es correcta, veremos la notificación a toast.
Manejar el token de autenticación en Vue
Ya con el token de autenticación generado, lo siguiente que tenemos que hacer es poder guardar el token de
alguna manera para poder manejarlo a lo largo de la aplicación; maneras hay muchas, y en este apartado vamos
presentar un mecanismo muy sencillo y flexible, que nos permitirá, una vez generado el token, poder consumir el
282
token fácilmente a lo largo de la aplicación, aparte de, obtener datos del usuario entre cualquier otro dato que
quieras suministrar desde la aplicación en Laravel.
Con esto, cuando comprobemos credenciales desde el recurso rest:
Auth::attempt($credentials)
Tendremos cargado los datos de usuario en la sesión y podremos inclusive, establecer el token de acceso:
app\Http\Controllers\Api\UserController.php
public function login(Request $request)
{
$credentials = [
'email' => $request->email,
'password' => $request->password
];
if (Auth::attempt($credentials)) {
$token = Auth::user()->createToken('myapptoken')->plainTextToken;
session()->put('token', $token);
return response()->json($token);
}
return response()->json("User/password invalid", 422);
}
Y, en el layout de blade que estamos usando para la aplicación de Vue, establecemos todos los datos que
queremos compartir:
resources\views\vue.blade.php
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="ie=edge">
<title>Vue</title>
</head>
<body>
@if (Auth::check())
<script>
window.Laravel = {!! json_encode([
283
'isLoggedIn' => true,
'user' => Auth::user(),
'token' => session('token'),
]) !!}
</script>
@else
<script>
window.Laravel = {!! json_encode([
'isLoggedIn' => false,
]) !!}
</script>
@endif
<div class="container">
<div id="app"></div>
</div>
@vite(['resources/js/vue/main.js'])
</body>
</html>
Es importante activar el servicio de:
bootstrap/app.php
$middleware->statefulApi();
Para que puedas emplear el servicio de la sesión y almacenar los datos, como puedes ver, estamos empleando
ambos sistemas de autenticación al mismo tiempo, el de SPA y el de las cookies sin problemas.
En el código anterior, se establece en una variable de JavaScript llamada "Laravel" que puede ser accedida
mediante el objeto window, para hacerla global; en el código anterior, establecemos:
● isLoggedIn, un booleano que indica si se está o no autenticado.
● user, para los datos del usuario.
● token, para el token de acceso.
Para poder usar los datos establecidos en el objeto window en la aplicación de Vue, vamos a establecerlos en el
componente padre:
resources\js\vue\App.vue
<script>
export default {
data() {
284
return {
isLoggedIn: false,
user: "",
token: "",
};
},
created() {
if (window.Laravel.isLoggedIn) {
this.isLoggedIn = true;
this.user = window.Laravel.user;
this.token = window.Laravel.token;
this.verified = window.Laravel.verified;
}
},
};
</script>
Y de esta manera, podremos usarlos de manera global en toda la aplicación y desde cualquier componente
mediante:
this.$root.<GLOBAL_PROPERTY>
Por ejemplo, para el token, sería:
this.$root.token
Redirecciones en el componente de login
Otro punto a favor que tenemos al establecer los datos de usuario de manera global en una variable y usarlos
desde un componente al momento de crear el mismo (mediante la función de created()), es que, los datos se
establecen de manera automática al cargar la página.
Finalmente, ya con todo el sistema establecido para manejar los datos del usuario autenticado desde Vue, al
momento de hacer un login, vamos a realizar una redirección:
resources\js\vue\componets\Auth\LoginComponent.vue
setTimeout(() => (window.location.href = "/vue"), 1500);
Con esto, estamos recargando toda la página para cargar los datos establecidos por Laravel mediante las
variables globales, esto lo colocamos en el login, al tener un login exitoso:
resources\js\vue\componets\Auth\LoginComponent.vue
submit() {
this.cleanErrorsForm();
this.$axios
285
.post("/api/user/login", this.form)
.then((res) => {
setTimeout(() => (window.location.href = "/vue"), 1500);
***
Tal cual puedes ver, hacemos una redirección un segundo y medio después, para que de tiempo de mostrarse la
notificación de Oruga UI.
En este mismo componente, vamos a realizar otra redirección, en este caso con Vue, si ya tenemos los datos
establecidos:
resources\js\vue\componets\Auth\LoginComponent.vue
created() {
if (this.$root.isLoggedIn) {
this.$router.push({ name: "list" });
}
},
Este es un comportamiento típico ya que, la página de login solamente puede ser accedida si el usuario no está
autenticado.
Enviar token en las peticiones
Ahora, ya con nuestro token, lo único que debemos hacer es, configurar el token de acceso desde cualquier
componente hijo; por ejemplo:
const config = {
headers: { Authorization: `Bearer ${this.$root.token}` },
};
//***
this.$axios.<METHOD>(<ROUTE>, config)
También, pudieras configurar el token de autenticación de manera global en axios:
axios.defaults.headers.common['Authorization'] = `Bearer ${this.$root.token}`;
Y eso sería todo lo que debemos de hacer, para consumir cualquier petición que se encuentre protegida por
autenticación; por ejemplo, la de listado:
resources\js\vue\componets\ListComponent.vue
listPage() {
this.isLoading = true;
const config = {
headers: { Authorization: `Bearer ${this.$root.token}` },
286
};
this.$axios
.get("/api/post?page=" + this.currentPage, config)
.then((res) => {
this.posts = res.data;
console.log(this.posts);
this.isLoading = false;
});
},
Y para realizar la prueba anterior, protege las rutas con autenticación, ya sean parciales o totales:
routes\api.php
Route::group(['middleware' => 'auth:sanctum'], function () {
Route::get('category/all', [CategoryController::class, 'all']);
Route::resource('category', CategoryController::class)->except(["create", "edit"]);
Route::resource('post', PostController::class)->except(["create", "edit"]);
Route::get('post/all', [PostController::class, 'all']);
Route::get('post/slug/{post:slug}', [PostController::class, 'slug']);
Route::get('category/slug/{slug}', [CategoryController::class, 'slug']);
Route::get('category/{category}/posts', [CategoryController::class, 'posts']);
});
Es importante notar que, con la activación del servicio de autenticación vía la SPA de Sanctum, no hay necesidad
de pasar el token de autenticación como mostramos en la implementación de
resources\js\vue\componets\ListComponent.vue y puede quedar exactamente igual a como estaba definida
inicialmente ya que, se estaría empleando la autenticación vía SPA; puedes probar lo explicado, comentando el
servicio anterior y verás que, al estar autenticado, la aplicación funcionará perfectamente.
Cerrar la sesión
Para implementar la opción de logout en la aplicación, crearemos una función que se encarga de eliminar la
sesión y con esto, la impresión del token establecida en resources\views\vue.blade.php y con esto, la
referencia generada al usuario autenticado en el App.vue:
app\Http\Controllers\Api\UserController.php
public function logout()
{
if($request->user()){
// auth()->user();
// $request->user()->currentAccessToken()->delete();
287
$request->user()->tokens()->delete();
}
session()->flush();
return response()->json('ok');
}
Si quieres emplear la autenticación mediante las cookies y la sesión, solamente debes emplear:
session()->flush();
Si quieres también emplear la autenticación por tokens (o estás empleando ambas) se emplea:
$request->user()->currentAccessToken()->delete();
Para eliminar el acceso al token actual (el configurado en el request), o para eliminar todos los tokens:
$request->user()->tokens()->delete();
También puedes ingresar al usuario autenticado mediante:
auth()->user();
Se emplea un condicional para verificar si el usuario está autenticado antes de eliminar el token (nuevamente, si
empleas ambos esquemas de autenticación al mismo tiempo pero no se suministra el token el auth()->user() o el
$request->user() daría null).
Creamos la ruta:
routes\api.php
Route::post('user/logout', [UserController::class, 'logout'])->middleware('auth:sanctum');
Para ingresar al logout, lógicamente el usuario tiene que estar autenticado, por lo tanto, se coloca el middleware
de autenticación de Sanctum.
Creamos un botón para cerrar la sesión, el cual colocaremos en el componente padre, para que pueda ser
reutilizado automáticamente en los componentes hijos:
resources\js\vue\App.vue
<template>
<nav class="bg-white border-b border-gray-100" >
<header>
<div class="flex gap-3 bg-gray-200">
<nav-item :to="{ name: 'index' }" title="Cursos" />
288
<router-link :to="{ name: 'login' }">login</router-link>
<router-link to="">register</router-link>
<o-button variant="danger" @click="logout">Close sesion</o-button>
</div>
</header>
</nav>
<router-view></router-view>
</template>
Y el script que implementa la función anterior:
logout(e) {
e.preventDefault();
this.$axios
.post("/api/user/logout")
.then(() => {
window.location.href = "/vue";
})
.catch(function () {
window.location.href = "/vue";
});
},
Como puedes darte cuenta, al realizar la petición HTTP, redireccionamos a la raíz de la aplicación, usamos
primitivas de JavaScript en vez de la navegación SPA para recargar toda la página y con esto, los tokens de
acceso.
Si vas a enviar el token de autenticación, la petición queda como:
const config = {
headers: {
Authorization: this.token
}
}
this.$axios.post("/logout", null, config)
El null hace referencia a los parámetros o datos que enviamos, que en este ejemplo no suministramos ningún
dato, y el de config es el header con el token.
Recuerda que para como tenemos actualmente la aplicación, al destruir la sesión, se pierde el acceso al token
especificado en:
resources\views\vue.blade.php
window.Laravel = {!! json_encode([
289
'isLoggedIn' => true,
'user' => Auth::user(),
'token' => session('token'),
]) !!}
Pero, el token sigue siendo válido a menos que se destruya mediante:
$request->user()->currentAccessToken()->delete();
$request->user()->tokens()->delete();
Como recomendación en base a la explicación anterior, prueba comentar la destrucción del token al momento del
logout, copia el token generado y haz pruebas de suministrando el token en el header y como es interpretado en
el servidor.
También pudieras usar la función de logout que se encuentra definida al instalar Laravel Breeze:
this.$axios.post("/logout")
Manejar el token de autenticación mediante una Cookie
Como indicamos anteriormente, actualmente tenemos una autenticación que podría ser considerada híbrida
empleando la autenticación vía SPA y por token de Sanctum; en este apartado, vamos a convertir ese servicio
como opcional, por lo tanto, ya queda de parte del lector y del proyecto que estés llevando a cabo si desea
habilitarlo o no, pero, no será obligatorio como actualmente requiere la implementación; para esto, usamos las
Cookies, el objetivo es, registrar en una Cookie el mismo esquema que tenemos actualmente:
{
"isLoggedIn": true,
"token": "***",
"user": {
"id": 1,
"name": "***",
"email": "***",
"email_verified_at": null,
"created_at": "***",
"updated_at": "***",
}
}
Poder manejar el token del usuario autenticado sin necesidad de establecer una sesión en el controlador es una
lógica común que usada por las aplicaciones hoy en día, que los datos del usuario autenticado, una vez
generados, se manejan desde el cliente, viene siendo el esquema más común que podemos seguir, y es el que
vamos a presentar a continuación.
En vez de vincular el token del usuario a la sesión en el servidor, vamos a usar el mecanismo de las cookies, con
el cual, podemos guardar y obtener el token del usuario, y cualquier cantidad de datos adicionales, directamente
desde el cliente.
290
Instalar vue3-cookies
Para manejar las cookies en Vue, necesitamos instalar un plugin que será el siguiente:
https://www.npmjs.com/package/vue3-cookies
Ejecutamos en la terminal:
$ npm install vue3-cookies --save
E inicializamos las cookies en el proyecto:
import { createApp } from "vue";
//tailwind
import '../../css/vue.css'
// Oruga
import Oruga from '@oruga-ui/oruga-next'
import '@oruga-ui/theme-oruga/dist/oruga.css'
//Material Design
import "@mdi/font/css/materialdesignicons.min.css"
import axios from 'axios'
import App from "./App.vue"
import router from "./router"
import VueCookies from 'vue3-cookies'
const app = createApp(App).use(Oruga).use(router)
app.use(VueCookies);
app.config.globalProperties.$axios = axios
window.axios = axios
app.mount("#app")
Ya con esto, podemos hacer uso de la Cookie y con esto, poder guardar valores:
this.$cookies.set(keyName,'value');
U obtenerlos:
$cookies.get(keyName)
291
Configurar vue3-cookies con los datos de autenticación
Vamos a crear una función para establecer los valores de usuario en el componente padre, con lo cual, podremos
usarlo en cualquier parte de la aplicación mediante los componentes hijos:
resources\js\vue\App.vue
setCookieAuth(data) {
this.$cookies.set("auth", data);
},
La misma, la usaremos desde el login y estableceremos los datos generados del usuario autenticado provistos
por la autenticación:
resources\js\vue\componets\Auth\Login.vue
submit() {
this.cleanErrorsForm();
return this.$axios
.post("/api/user/login", this.form)
.then((res) => {
this.$root.setCookieAuth({
isLoggedIn: res.data.isLoggedIn,
token: res.data.token,
user: res.data.user,
});
***
}
Con esto, tenemos que establecer desde la función de login en el controlador, los datos esperados:
app\Http\Controllers\Api\UserController.php
public function login(Request $request)
{
$credentials = [
'email' => $request->email,
'password' => $request->password
];
if (Auth::attempt($credentials)) {
$token = Auth::user()->createToken('myapptoken')->plainTextToken;
session()->put('token', $token);
return response()->json([
'isLoggedIn' => true,
292
'user' => auth()->user(),
'token' => $token,
]);
}
return response()->json("Usuario y/o contraseña inválido", 422);
}
Ahora, al realizar el login, tendremos los datos del usuario autenticado en la cookie de la aplicación, los cuales
podemos consumir sin ningún problema a lo largo de la aplicación en Vue y son completamente independientes
de la sesión del servidor.
Logout: Destruir la cookie del usuario
Ahora, con el token de usuario y demás información de usuario registrado en una Cookie, lo siguiente que vamos
a realizar es, al cerrar la sesión, también destruir la cookie del usuario:
resources\js\vue\App.vue
logout() {
this.$axios.post("/api/user/logout").then((res) => {
this.setCookieAuth("");
window.location.href = "/vue/login";
});
},
Es importante notar que, si no tienes habilitado el servicio de Sanctum para poder usar la sesión en la rest api, no
sería necesario destruir la sesión si no solamente la cookie, por lo tanto, la operación a realizar, solo seria del
lado del cliente.
Verificar el token del usuario
La desventaja que tenemos al trabajar con información de usuario del lado del cliente es que, debemos de
verificar cuando los datos son válidos; el token de autenticación no es eterno y se puede eliminar de manera
programática mediante Laravel o directamente desde la base de datos, por lo tanto, siempre es buena idea
revisar si el token guardado en la Cookie es válido o ya no existe en el servidor; claro está, que este paso no
sería necesario si usamos la sesión de usuario (y no tengamos algún proceso para eliminar el token mientras
usamos la sesión).
Para verificar el token, debemos de entender cómo maneja el token de usuario Laravel Sanctum; por protección,
Laravel Sanctum guarda el token generado en una tabla en la base de datos en texto plano, y es como lo
estamos manejando a través de los datos compartidos:
Auth::user()->createToken('test')->plainTextToken;
Lucirá similar al siguiente:
1|4j1w6oopmFYkbEV7GL5MLMBBaO4jXc0nawQu39nO
293
El token en texto plano, está formada en dos partes:
<ID>|<TOKEN>
El ID y el token generado como tal, el ID corresponde al identificador que tenemos en la tabla de la base de datos
llamada personal_access_tokens, en el ejemplo anterior, sería el ID de 1
Pero, si revisamos en la base de datos, el token está convertido en un HASH:
Figura 14-17: Token de usuario
Y esto último es detallado en la documentación oficial de Laravel Sanctum:
***API tokens are hashed using SHA-256***
https://laravel.com/docs/master/sanctum
Teniendo el token en texto plano:
1|4j1w6oopmFYkbEV7GL5MLMBBaO4jXc0nawQu39nO
Entonces, para verificar si el token es válido o no, basta con revisar si existe su registro en la tabla de
personal_access_tokens; pero, debemos de convertir el token en texto plano a un hash para hacer dicha
comprobación.
Para convertir el token a un HASH y con esto, poder usarlo en una consulta SQL, usamos la función la función
de:
hash('sha256', <TOKEN>)
Con el HASH del TOKEN, ahora lo podemos usar el siguiente método para obtener una referencia al TOKEN que
está registrado en la base de datos:
[$id, $token] = explode('|', request('token')); // =
[1,4j1w6oopmFYkbEV7GL5MLMBBaO4jXc0nawQu39nO] 1|4j1w6oopmFYkbEV7GL5MLMBBaO4jXc0nawQu39nO
$token = PersonalAccessToken::where('token', hash('sha256', $token))->first();
Es importante notar que, la consulta puede devolver un valor nulo, lo que significa que el token no existe en la
base de datos; si el token no existe, es debido a que, el token es invalido o fue eliminado de la base de datos.
También es importante notar que, para generar el hash del token, solamente debemos de usar la sección del
String que corresponde al Token y no junto con el ID y el pipe:
294
<ID>|<TOKEN>
Si la consulta anterior devuelve un token, entonces, tenemos acceso al usuario (una instancia del modelo
definido en tokanable_type que en nuestra implementación es un usuario, esto es una relación polimórfica que
trataremos más adelante en otro capítulo) y esto sería condición suficiente para indicar que el token es válido:
$user = $token->tokenable;
Así que, pensando en lo explicado anteriormente, podemos crear un método el en servidor como el siguiente:
app\Http\Controllers\Api\UserController.php
public function checkToken()
{
try {
[$id, $token] = explode('|', request('token'));
$tokenHash = hash('sha256', $token);
$tokenModel = PersonalAccessToken::where('token', $tokenHash)->first();
if ($tokenModel->tokenable) {
Auth::login($tokenModel->tokenable);
return response()->json([
'isLoggedIn' => true
]);
}
//dd($tokenModel->tokenable);
} catch (\Throwable $th) {
}
return response()->json("Usuario inválido", 422);
}
Explicación del código anterior
● Una vez obtenida la instancia del token, iniciamos sesión mediante el método login() que recibe como
parámetro una instancia del usuario (en este caso $tokenModel->tokenable).
● Usamos los bloques try/catch y que, muchas operaciones pueden dar problemas al momento de realizar
las operaciones anteriores; por ejemplo, el uso de la función explote(), el acceso a las propiedades del
token, etc, si ocurren problemas, indicamos que el usuario es inválido.
Y la ruta del método anterior:
routes\web.php
Route::post('user/token-check', [UserController::class, 'checkToken']);
295
Esta función, la usaremos al momento de entrar en la aplicación por primera vez, si la función de verificación es
exitosa, el flujo de la aplicación sigue su curso, caso contrario, cerramos sesión y redireccionamos a la vista de
login:
resources\js\vue\App.vue
created() {
if (window.Laravel.isLoggedin) {
this.isLoggedIn = true;
this.user = window.Laravel.user;
this.token = window.Laravel.token;
this.verified = window.Laravel.verified;
this.setCookieAuth({
isLoggedIn: this.isLoggedIn,
token: this.token,
user: this.user,
});
} else {
const auth = this.$cookies.get('auth')
if(auth) {
this.isLoggedIn = true
this.user=auth.user
this.token=auth.token
// verification token
this.$axios.post(this.$root.urls.tokenCheck,{
token: auth.token
}). then(() =>{
console.log('tokenCheck')
}).catch(()=>{
this.setCookieAuth('');
window.location.href = '/vue/login'
})
}
},
Logout: Destruir el token del usuario
Al momento de realizar el logout del usuario, vamos a eliminar el token del usuario, para esto, tenemos dos
opciones, eliminarlos todos:
Auth::user()->tokens()->delete();
O solamente uno, dado el ID del token:
Auth::user()->tokens()->where('id', $id)->delete();
Vamos a ir por la segunda opción:
296
app\Http\Controllers\Api\UserController.php
public function logout()
{
// dd( Auth::user()->tokens()->get());
try {
[$id, $token] = explode('|', request('token'));
Auth::user()->tokens()->where('id', $id)->delete();
} catch (\Throwable $th) {
}
session()->flush();
return response()->json("Ok");
}
Y desde Vue, mandamos el token:
resources\js\vue\App.vue
logout() {
this.$axios.post("/api/user/logout",{
token: this.token
}).then((res) => {
this.setCookieAuth("");
window.location.href = "/vue/login";
});
},
Unificar Token y sesión de Sanctum
En este apartado, vamos a completar la implementación para que la autenticación en Vue funciona tanto con la
sesión de Laravel Sanctum y junto con la Cookie; la lógica va a ser la siguiente:
● Si la sesión está activa y tenemos los datos de usuarios, entonces, tomamos los datos de usuario desde
allí e inicializamos las propiedades del componente de App.vue en vez; no usaremos los datos de la
cookie y actualizamos la cookie con dichos datos, ya que, de esta manera tenemos los datos actualizados
directamente del servidor.
● Si la sesión no está activa, pero tenemos datos en la cookie, entonces inicializamos las propiedades del
componente de App.vue y verificaremos en el servidor si los datos en la cookie son válidos.
La lógica sería muy sencilla, ya que, el proveedor de los datos puede ser o la sesión o la cookie y dicha
verificación se realizará en un solo lugar, en el App.vue y será transparente para el resto de los componentes:
resources\js\vue\App.vue
created() {
if (window.Laravel.isLoggedin) {
this.isLoggedIn = true;
297
this.user = window.Laravel.user;
this.token = window.Laravel.token;
this.verified = window.Laravel.verified;
this.setCookieAuth({
isLoggedIn: this.isLoggedIn,
token: this.token,
user: this.user,
});
} else {
const auth = this.$cookies.get("auth");
if (auth) {
this.isLoggedIn = true;
this.user = auth.user;
this.token = auth.token;
this.verified = auth.verified;
this.$axios
.post("/api/user/token-check", {
token: auth.token,
})
.then(() => {})
.catch(() => {
this.setCookieAuth("");
window.location.href = "/vue/login";
});
}
}
},
Proteger rutas por autenticación requerida
Ya que finalmente tenemos la integración lista del uso del token a lo largo de la aplicación. lo siguiente que
debemos de hacer es proteger las rutas con la autenticación requerida; para esto, pudiéramos hacerla localmente
en cada uno de los componentes a usar, como serían el de resources\js\vue\componets\SaveComponent.vue
o el de resources\js\vue\componets\ListComponent.vue, aunque, lo mejor es protegerlo en un nivel más
arriba como pudiera ser en este caso el de las rutas, que viene siendo el componente por excelencia para
manejar esta lógica; para esto, tenemos que aplicar las reglas de verificación por parte del usuario antes de
ingresar a las rutas; para esto, tenemos el uso de la función beforeEach() en Vue Router:
router.beforeEach(async (to, from, next) => {
next()
})
Esta función se llama antes de ingresar a cualquier otra de la aplicación, por lo tanto, podemos aplicar cualquier
condición para cancelar la redirección o para enviar a otra ruta, por ejemplo, la de login.
La función de beforeEach() recibe 3 parámetros:
1. to, especifica la ruta destino.
298
2. from, especifica la ruta desde.
3. next, es una función la cual debemos de invocar para ir a la ruta destino:
a. Sin un argumento, va a la ruta estipulada originalmente.
b. Con un argumento, debemos de especificar la ruta destino.
Para aplicar las reglas de validación, necesitamos acceder a la cookie manejada por Vue, y para acceder a la
misma desde el archivo de rutas, debemos de importarlo de la siguiente manera:
import { useCookies } from "vue3-cookies";
const { cookies } = useCookies();
Ya que, el objeto de this no tiene ámbito en este archivo al no ser un componente de la aplicación en Vue.
Finalmente, la implementación queda como:
resources\js\vue\router.js
import { useCookies } from "vue3-cookies";
const { cookies } = useCookies();
router.beforeEach(async (to, from, next) => {
const auth = cookies.get('auth');
if (!auth && to.name !== 'login') {
return next({ name: 'login' })
}
next()
})
Es realmente sencilla, en la condición de:
!auth && to.name !== 'login'
Verificamos si el usuario no está autenticado y si la ruta a la cual se quiere acceder no es la de login, si es el
caso, redirecciona al componente de login:
return next({ name: 'login' })
Caso contrario, va a la ruta original, en este caso, el usuario si está autenticado y paso todas las reglas definidas
anteriormente:
next()
Detalles funcionales finales
Recuerda que para cada recurso que quieras proteger en tu Rest Api, debes de enviar el token, por ejemplo:
299
resources\js\vue\componets\ListComponent.vue
listPage() {
const config = {
headers: { Authorization: "Bearer " + this.$root.token },
};
this.isLoading = true;
this.$axios
.get("/api/post?page=" + this.currentPage, config)
.then((res) => {
this.posts = res.data;
this.isLoading = false;
});
},
Esto es importante ya que, si has seguido los pasos detallados en el libro, puedes mantener activado o
desactivado el servicio de autenticación SPA de Sanctum, y toda la aplicación funcionará correctamente, ya sea
empleando dicho servicio, o el de las cookies que implementamos antes.
Otro esquema que tenemos para hacer el logout del token, es eliminandolo directamente desde la tabla:
PersonalAccessToken::where('id', $id)->delete();
session()->flush();
Detalles visuales
En este apartado, vamos a trabajar en la interfaz gráfica para mejorar la experiencia de la aplicación para ello,
usaremos Tailwind principalmente en el proceso ya que, aunque estamos usando Oruga UI para los
componentes, para los pequeños detalles o componentes que no existen en Oruga UI, usaremos las clases de
Tailwind.
Ventana de login
Comencemos con la ventana de login, en la cual, realizaremos el diseño típico de colocar el formulario en una
carta centrada con bordes redondeados:
resources\js\vue\componets\Auth\LoginComponent.vue
<template>
<div class="min-h-screen flex flex-col sm:justify-center items-center bg-gray-100">
<div
class="
w-full
sm:max-w-md
mt-6
px-6
py-6
300
bg-white
shadow-md
overflow-hidden
sm:rounded-md
"
>
<form @submit.prevent="submit">
<h2 class="mt-3 mb-6 text-center text-3xl tracking-tight font-bold text-gray-900">
Sing in to your account
</h2>
<o-field
:variant="errors.login ? 'danger' : 'primary'"
message=""
label="Username"
>
<o-input v-model="form.email"> </o-input>
</o-field>
<o-field
:variant="errors.login ? 'danger' : 'primary'"
:message="errors.login"
label="Password"
>
<o-input v-model="form.password" type="password"> </o-input>
</o-field>
<o-button class="float-right" variant="primary"
native-type="submit">Send</o-button>
</form>
</div>
</div>
</template>
***
Y tendremos:
301
Figura 14-18: Ventana para el login con estilo
Container
La clase container, al tenerla configurada a nivel de:
resources\views\vue.blade.php
Se aplica a lo largo de toda la aplicación en Vue, lo cual es un problema para la ventana de login; así que,
quitaremos el DIV container del vue.blade.php.
Y lo colocaremos en cada uno de los componentes necesarios para que abarque el contenido:
resources\js\vue\componets\ListComponent.vue
resources\js\vue\componets\SaveComponent.vue
<div class="container mx-auto">
</div>
Navbar
En este apartado, vamos a trabajar con el navbar, es importante notar que, a partir de esta implementación
empezaremos a usar más y más los flex y sus clases derivadas para alinear el contenido:
justify-* para justificar el contenido a lo largo del contenedor:
302
https://tailwindcss.com/docs/justify-content
items-* para justificar el contenido a lo largo del eje transversal del contenedor:
https://tailwindcss.com/docs/align-items
También recuerda que, apenas al colocar la clase de "flex" por defecto, tenemos una alineación por filas
(flex-row) y también podemos usar la alineación en base a columnas usando la clase flex-col.
Además de definir los PADDING, MARGIN y tamaños en cada uno de los contenedores.
Es importante que entiendas el funcionamiento base de las mismas y realizar tus pruebas con la estructura que
vamos a definir para que comprendas el código presentado en este apartado; por aquí, puedes ver el video en el
cual realizamos pruebas con el código generado en este apartado:
https://www.youtube.com/watch?v=SlFd6WQ9wH4
Ahora, vamos a construir una cabecera con algo más de estilo, la cual dividiremos en dos secciones, el del lado
izquierdo, lo usaremos para colocar un logo, y el derecho para indicar las opciones a las cuales, también
aplicamos un estilo:
resources\js\vue\App.vue
<nav class="bg-white border-b border-gray-100">
<header class="max-w-7xl px-4 sm:px-6 lg:px-8">
<div class="flex">
<div class="flex items-center">
Logo
</div>
<div class="max-w-7xl mx-auto py-4 px-4 sm:px-6">
</div>
</div>
</header>
</nav>
***
En la estructura anterior, alineamos mediante los flexs de CSS y definimos los espaciados correspondientes para
realizar las separaciones del resto de los elementos que ya vamos a definir; una vez aplicado, veremos la
siguiente distribución:
Figura 14-19: Distribución para el navbar
303
Navbar: Enlaces de navegación
Ahora, vamos a configurar un estilo para los enlaces de navegación:
<nav class="bg-white border-b border-gray-100">
<header class="max-w-7xl px-4 sm:px-6 lg:px-8">
<div class="flex">
<div class="flex items-center">
Logo
</div>
<div class="max-w-7xl mx-auto py-4 px-4 sm:px-6">
<router-link
class="
inline-flex
uppercase
border-b-2
text-sm
leading-5
mx-3
px-4
py-1
text-gray-600 text-center
font-bold
hover:text-gray-900 hover:border-gray-700 hover:-translate-y-1
duration-150
transition-all
"
v-if="!$root.isLoggedIn"
:to="{ name: 'login' }"
>Login</router-link
>
<router-link
class="
inline-flex
uppercase
border-b-2
text-sm
leading-5
mx-3
px-4
py-1
text-gray-600 text-center
font-bold
hover:text-gray-900 hover:border-gray-700 hover:-translate-y-1
duration-150
transition-all
"
304
v-if="$root.isLoggedIn"
:to="{ name: 'list' }"
>Post</router-link
>
<o-button v-if="$root.isLoggedIn" variant="danger" @click="logout">
Logout
</o-button>
</div>
</div>
</header>
</nav>
También aplicamos un efecto para el hover al posicionar el cursor sobre los enlaces para variar el estilo ya
aplicado.
Navbar: Logo
Para el logo, en el libro usaremos un icono representativo al logo de Vue:
<nav class="bg-white border-b border-gray-100">
<header class="max-w-7xl px-4 sm:px-6 lg:px-8">
<div class="flex">
<div class="flex items-center">
<svg
xmlns="http://www.w3.org/2000/svg"
xmlns:xlink="http://www.w3.org/1999/xlink"
width="40"
height="35"
viewBox="0 0 262 227"
>
<g id="Vue.js_logo_strokes" fill="none" fill-rule="evenodd">
<g id="Path-2">
<polyline
class="outer"
stroke="#4B8"
stroke-width="46"
points="12.19 -24.031 131 181 250.351 -26.016"
/>
</g>
<g id="Path-3" transform="translate(52)">
<polyline
class="inner"
stroke="#354"
stroke-width="42"
points="15.797 -14.056 79 94 142.83 -17.863"
/>
305
</g>
</g>
</svg>
</div>
<div class="max-w-7xl mx-auto py-4 px-4 sm:px-6">
***
</div>
</div>
</header>
</nav>
El logo SVG fue tomado y adaptado de:
https://codepen.io/dimshik/pen/QomPYX
Navbar: Avatar
Para la estructura del avatar, que solamente mostraremos cuando estamos autenticados, será en base a
columnas usando los flexs, para colocar el circulo de color, en donde generalmente se coloca la imagen o avatar
del usuario y abajo colocamos el nombre del usuario:
<nav class="bg-white border-b border-gray-100">
<header class="max-w-7xl px-4 sm:px-6 lg:px-8">
<div class="flex">
<div class="flex items-center">
*** LOGO
</div>
<div class="w-full flex py-4 px-4 sm:px-6 justify-between items-center">
<!-- <div></div> -->
<div class="flex h-8 items-center">
*** LINKS
</div>
<div class="flex flex-col items-center" v-if="$root.isLoggedIn">
<div
class="
rounded-full
w-9
h-9
bg-blue-300
text-center
p-1
font-bold
"
>
{{ $root.user.name.substr(0, 2).toUpperCase() }}
</div>
<p>
306
{{ $root.user.name }}
</p>
</div>
</div>
</div>
</header>
</nav>
Y tendremos:
Figura 14-20: Avatar
Navbar: Detalles finales
Al tener 3 elementos que alinear:
● Logo
● Enlaces
● Avatar
Debemos de alinear los elementos mencionados anteriormente de una manera lógica; así que, para ello, vamos
a usar los flex cada vez que sea necesario, si no conoces el uso de los flex, se recomienda que tengas una
introducción a los mismos para que puedas entender la siguiente estructura; finalmente, el código completo:
<nav class="bg-white border-b border-gray-100">
<header class="max-w-7xl px-4 sm:px-6 lg:px-8">
<div class="flex">
<div class="flex items-center">
<svg
xmlns="http://www.w3.org/2000/svg"
xmlns:xlink="http://www.w3.org/1999/xlink"
width="40"
height="35"
viewBox="0 0 262 227"
>
<g id="Vue.js_logo_strokes" fill="none" fill-rule="evenodd">
<g id="Path-2">
<polyline
class="outer"
stroke="#4B8"
stroke-width="46"
points="12.19 -24.031 131 181 250.351 -26.016"
307
/>
</g>
<g id="Path-3" transform="translate(52)">
<polyline
class="inner"
stroke="#354"
stroke-width="42"
points="15.797 -14.056 79 94 142.83 -17.863"
/>
</g>
</g>
</svg>
</div>
<div class="w-full flex py-4 px-4 sm:px-6 justify-between items-center">
<!-- <div></div> -->
<div class="flex h-8 items-center">
<router-link
class="
inline-flex
uppercase
border-b-2
text-sm
leading-5
mx-3
px-4
py-1
text-gray-600 text-center
font-bold
hover:text-gray-900 hover:border-gray-700 hover:-translate-y-1
duration-150
transition-all
"
v-if="!$root.isLoggedIn"
:to="{ name: 'login' }"
>Login</router-link
>
<router-link
class="
inline-flex
uppercase
border-b-2
text-sm
leading-5
mx-3
px-4
py-1
308
text-gray-600 text-center
font-bold
hover:text-gray-900 hover:border-gray-700 hover:-translate-y-1
duration-150
transition-all
"
v-if="$root.isLoggedIn"
:to="{ name: 'list' }"
>Post</router-link
>
<o-button
v-if="$root.isLoggedIn"
variant="danger"
@click="logout"
>
Logout
</o-button>
</div>
<div class="flex flex-col items-center" v-if="$root.isLoggedIn">
<div
class="
rounded-full
w-9
h-9
bg-blue-300
text-center
p-1
font-bold
"
>
{{ $root.user.name.substr(0, 2).toUpperCase() }}
</div>
<p>
{{ $root.user.name }}
</p>
</div>
</div>
</div>
</header>
</nav>
Y tendremos:
309
Figura 14-21: Navbar final
Carta para los componentes CRUD
Para evitar que en el listado y formulario de las publicaciones tenga un aspecto tan plano, vamos a colocar una
carta que englobe ambos componentes:
resources\js\vue\componets\ListComponent.vue
resources\js\vue\componets\SaveComponent.vue
<div class="container mx-auto">
<div class="mt-6 mb-2 px-6 py-4 bg-white shadow-md rounded-md">
***CONTENT
</div>
</div>
Al igual que un estilo para los títulos en mayúscula en general:
resources\css\vue.css
h1{
@apply text-4xl font-bold my-3
}
Y tendremos:
310
Figura 14-22: Componente de carta
Bloquear botón de login al momento del submit
Ya que el botón de login, al presionar el mismo, tarda un segundo y medio de demora para mostrar el mensaje de
confirmación de la acción y a esto, le sumamos el tiempo de la petición axios y luego, si el login es exitoso se
redirecciona al componente de listado, vamos a bloquear el botón de submit al presionar sobre el mismo, con
esto, se evita que el usuario pueda presionar por error varias veces sobre el botón en el tiempo de retardo. Para
esto, usaremos una propiedad, que, al presionar sobre el botón, la colocaremos en true y se bloqueará el botón;
finalmente al obtener la respuesta, restableceremos dicha propiedad:
<o-button :disabled="disabledBotton" class="float-right" variant="primary"
native-type="submit">Send</o-button>
***
<script>
submit() {
this.disabledBotton = true;
this.cleanErrorsForm();
this.$axios
311
.post("/api/user/login", this.form)
.then((res) => {
this.$root.setCookieAuth(res.data);
setTimeout(() => {
this.disabledBotton = false;
window.location.href = "/vue";
}, 1500);
this.$oruga.notification.open({
message: "Login success",
position: "bottom-right",
duration: 1000,
closable: true,
});
})
.catch((error) => {
console.log(error);
this.disabledBotton = false;
if (error.response.data) {
this.errors.login = error.response.data;
}
});
},
***
data() {
return {
form: {
email: "",
password: "",
},
disabledBotton: false,
errors: {
login: "",
},
};
},
</script>
Integrar la importación @ en vue en cualquier app en Laravel
Cómo comentamos al inicio del libro, al tener un proyecto en Laravel, también tenemos la integración con Node
de manera directa, es decir, tenemos acceso a todo el ecosistema en Node en un proyecto en Laravel, parte de
este ecosistema es Vue, que, aunque lo más recomendado es si quieres emplear Vue es emplear Laravel Inertia
del cual ya tengo un libro aparte:
https://www.desarrollolibre.net/libros/primeros-pasos-laravel-inertia
312
Hay muchos motivos por los cuales no quieras emplear Laravel Inertia, ya sea porque para la creación de tu
proyecto en en Laravel vas a disponer de una Rest Api la cual va a ser común, mediante una aplicación en
Laravel, tal cual hicimos nosotros antes, lo cual es particularmente útil si luego quieres crear otras aplicaciones
que consuman esta Rest Api, tal cual yo hice con la app de Academia, que mediante una Rest Api, cree una
aplicación en Vue y en Flutter:
https://academy.desarrollolibre.net/
https://play.google.com/store/apps/details?id=com.desarrollo.libre
O por sencillez y simplemente no quieres emplear Inertia con todo los paquetes y lógica adicional que esta trae.
Uno de los problemas, que tenemos actualmente, es que, al momento de importar los paquetes, tenemos que
movernos entre directorios:
import BookItem from "../../components/books/ItemComponent.vue";
Lo cual, es un problema si queremos luego reorganizar los componentes, tendremos seguramente que variar las
importaciones; al crear un proyecto en Vue mediante la CLI, automáticamente tenemos acceso a un comodín @
que nos permite importar paquetes de manera absoluta sin importar en donde se encuentre el paquete, el
proyecto en Vue que tenemos, no fue creado mediante la CLI si no directamente mediante Node, pero, aun así,
podemos simular este comportamiento implementando dicha funcionalidad, para eso, agregamos el siguiente
cocidos en nuestro archivo Vite:
vite.config.js
export default defineConfig({
plugins: [
vue(),
***
],
resolve: {
alias: {
'@': '/resources/js/vue',
'vue': 'vue/dist/vue.esm-bundler.js'
},
},
});
El cual hace referencia al directorio o carpeta raíz en donde van a estar nuestros componentes, ya a partir de
este, ahora podemos realizar estas importaciones de manera absoluta, por ejemplo:
import BookItem from "@/components/books/ItemComponent.vue";
313
Código fuente del capítulo:
https://github.com/libredesarrollo/book-course-laravel-base-api-11/releases/tag/v0.3
Capítulo 15: Caché
En cualquier sistema, es fundamental manejar la gestión de los recursos para mejorar el rendimiento en general
del sistema; uno de los puntos más importantes en un sistema es sobre los tipos de peticiones que se repiten
consecutivamente; un buen ejemplo de esto es un recurso rest de una rest api; por ejemplo un recurso que
siempre devuelve un mismo tipo de listado, es un buen candidato para poder mejorar su desempeño; para
analizar este punto suponte que tenemos un recurso que devuelve unos 10000 registros o más:
app\Http\Controllers\Api\PostController.php
class PostController extends Controller
{
public function all(): JsonResponse
{
return response()->json(Post::get());
}
}
Podemos mejorar el desempeño del mismo, guardando estos datos en caché; en Laravel, el manejo de la caché
es una funcionalidad crucial para mejorar el rendimiento de nuestras aplicaciones web. Laravel ofrece soporte
para múltiples sistemas de caché, incluyendo el almacenamiento en memoria, en disco, en base de datos e
inclusive en sistemas como Redis. Esto nos permite almacenar de manera temporal datos que se consumen con
frecuencia para acelerar el tiempo de respuesta de nuestras aplicaciones y con esto, consumir menos recursos.
En resumen, almacenar datos en caché reduce la cantidad de veces que se necesita procesar una solicitud
repetida y optimiza el tiempo de respuesta de la aplicación, ya que los datos almacenados en caché se pueden
recuperar rápidamente sin tener que ejecutar el proceso que se encarga de generar dichos datos;
tradicionalmente, sería el acceso a la base de datos, pero, puede ser cualquier otro como un HTML.
Uso básico de la caché
Con la cache en Laravel, tenemos distintos métodos y opciones que podemos utilizar para adaptarlo a distintos
esquemas; veamos los principales:
● get()
● has()
● put()
● putMany()
● add()
● pull()
● many()
● remember()
● rememberForever()
● increment()
314
● decrement()
● forever()
● forget()
● flush()
Todos estos métodos, están disponibles mediante el Facade de la caché; todos estos métodos en caché tienen
en común que reciben una key (a excepción del método putMany()), para poder acceder a la cache; por ejemplo,
si quieres guardar el detalle de un post, esta key puede ser post o post_detail por dar un ejemplo, si quieres un
listado de post, puede ser posts o posts_index; y usando esta key, es que podemos realizar una referencia a la
caché.
Cache::get()
El método get() se usa para recuperar elementos de la caché. Si el elemento no existe en el caché, se devolverá
un valor nulo:
$value = Cache::get('key');
De manera opcional, se puede pasar un segundo argumento que especifique el valor predeterminado que será
utilizado si el elemento no existe:
$value = Cache::get('key', 'default');
Cache::has()
El método has() se usa para determinar si existe un elemento en el caché; devuelve false si el valor no existe y
true en caso contrario:
Cache::has('key');
Cache::put()
El método put() acepta tres parámetros:
1. key/clave.
2. Duración de la caché.
3. Los datos que se van a almacenar en caché.
Cache::put(key, data, duration)
Por ejemplo:
Cache::put('key', 'value', $seconds = 10);
// Cache::put('key', 'value', now()->addMinutes(10));
Cache::putMany()
El método putMany() almacena un array de datos en la caché, por lo demás, tiene los mismos parámetros que el
método put() a excepción de la key:
315
Cache::putMany(Post::all(), now()->addMinutes(10));
Cache::add()
El método add() solo agrega el elemento al caché si aún no existe en el almacén de caché. El método devolverá
verdadero si el elemento se agrega realmente al caché, caso contrario, devuelve falso:
Cache::add('key', 'value', now()->addMinutes(10));
Cache::pull()
El método pull() podemos usarlo si necesita recuperar un elemento del caché y luego eliminarlo; al igual que el
método get(), retornará un valor nulo si el elemento no existe en el caché:
$value = Cache::pull('key');
Cache::many()
El método many() se utiliza para recuperar múltiples datos en base a un array de keys:
const $keys = [
'post_1',
'post_2',
'post_3',
***
];
Cache::many(keys);
Cache::remember()
Muchas veces es posible que desee recuperar un elemento del caché, pero también almacenar un valor
predeterminado si el elemento solicitado no existe. y para esto podemos utilizar el método de remember() que
acepta tres parámetros:
1. La key.
2. Duración de la caché.
3. Los datos para recuperar si no se encuentran los mismos.
Cache::remember('posts', now()->addMinutes(10), function(){
return Post::all();
});
En el script anterior, la función devuelve todos los posts (Post::all()) si no existen los datos en caché.
Cache::rememberForever()
Puede usar el método rememberForever() para recuperar un elemento del caché o almacenarlo para siempre
(no hay un parámetro para pasar la duración de la caché) si no existe:
316
$value = Cache::rememberForever('posts', function () {
return Post::all();
});
Incremento o disminución de los valores en caché
Puedes cambiar los valores de un valor entero almacenado en la caché utilizando los métodos de incremento y
decremento, respectivamente:
Cache::increment('key'); //$value = 1
Cache::increment('key', $value);
Cache::decrement('key'); //$value = 1
Cache::decrement('key', $value);
Cache::forever()
El método forever() almacena los datos en la caché para siempre sin especificar ninguna duración:
Cache::forever('key', 'value');
Cache::forget()
El método forget() elimina un elemento de la caché con un parámetro clave especificado:
Cache::forget('key');
Cache::flush()
Este método borra todos los elementos de la caché:
Cache::flush();
Caso práctico
Hay muchos enfoques que podemos usar al momento de usar la cache en Laravel; uno de los clásicos es, al
momento de realizar una consulta hacemos los siguientes pasos:
1. Verificamos si ya existen almacenados en la caché.
2. Si existen, se devuelven los datos en caché.
3. Si no existen, se realiza la consulta a la base de datos y con la respuesta de la base de datos, se usa
tanto para establecer en la caché y como parte de la respuesta.
Contenido JSON en Rest Api
Teniendo esto en mente, podemos cambiar el método de all() vista anteriormente en nuestra Rest Api para que
luzca como:
app\Http\Controllers\Api\PostController.php
317
class PostController extends Controller
{
public function all(): JsonResponse
{
if (Cache::has('posts_index')) {
return response()->json(Cache::get('posts_index'));
} else {
$posts = Post::get();
Cache::put('posts_index', $posts);
return response()->json($posts);
}
}
}
O también, podemos usar el método de remember() que permite tanto obtener como insertar los datos:
class PostController extends Controller
{
public function all(): JsonResponse
{
return response()->json(Cache::remember('posts_index', now()->addMinutes(10), function
() {
return Post::all();
}));
}
}
Es importante no hacer excesos de operaciones sobre el método de remember() ya que, si hacemos la
operación de convertir la respuesta de un JSON dentro del método mencionado:
class PostController extends Controller
{
public function all(): JsonResponse
{
return Cache::remember('posts_index', now()->addMinutes(10), function () {
return
});
}
}
Puede dar excepciones como la siguiente:
Allowed memory size of 536870912 bytes exhausted (tried to allocate 174067712 bytes)
Puedes acortar un poco la condición; por ejemplo, algo como:
class PostController extends Controller
318
{
public function all(): JsonResponse
{
return Cache::remember('posts_index', now()->addMinutes(10), function () {
return response()->json(Post::where('id','>', 1000)->get());
});
}
}
Para que veas que no ocurriría la excepción anterior, ya que, al trabajar con un conjunto menor de registros,
también el consumo de recursos del computador es menor.
Recuerda que puedes generar datos de prueba con:
database\seeders\DatabaseSeeder.php
class DatabaseSeeder extends Seeder
{
public function run(): void
{
***
Post::factory(10000)->create();
}
}
Para el caso de la Rest Api, puedes usar programas como Postman en donde ves el tiempo que tardó en
realizarse la consulta:
319
Figura 15-1: Petición con axios a la Rest Api
Es importante notar que, estamos en un ambiente local, de desarrollo, con recursos de cómputos mucho más
altos que lo que emplearemos en el mundo real en una aplicación en un servidor en producción recibiendo
múltiples consultas, por lo tanto, no veremos una variación importante en los tiempos entre la cache con la base
de datos.
Contenido HTML o fragmento de vista
También podemos guardar un contenido HTML con el mismo esquema presentado antes en la Rest Api; la
función de render sobre la vista permite retornar el contenido de la página en un String, que es lo que luego se
establece en la caché:
app\Http\Controllers\web\BlogController.php
use Illuminate\Support\Facades\Cache;
***
class BlogController extends Controller
{
public function show(Post $post): String
{
if (Cache::has('post_show_' . $post->id)) {
return Cache::get('post_show_' . $post->id);
} else {
$cacheView = view('blog.show', compact('post'))->render();
Cache::put('post_show_' . $post->id, $cacheView);
return $cacheView;
}
// return view('blog.show', compact('post'));
}
}
Si empleamos el método de remember() o rememberForever(), la implementación queda mucho más simple:
return Cache::rememberForever('post_show_'.$post->id, function () use($post) {
return view('blog.show', ['post' => $post])->render();;
});
Para completar el ejercicio, la caché que estamos almacenados nunca vence, podemos eliminar la misma de
manera manual cuando actualizamos el post, que es el único caso en el cual es necesario eliminar la caché:
app\Http\Controllers\Dashboard\PostController.php
public function update(PutRequest $request, Post $post)
320
{
$data = $request->validated();
// image
if (isset($data['image'])) {
$data['image'] = $filename = time() . '.' . $data['image']->extension();
$request->image->move(public_path('uploads/posts'), $filename);
}
// image
Cache::forget('post_show_'.$post->id);
$post->update($data);
return to_route('post.index')->with('status', 'Post updated');
}
Por supuesto, según el propósito de la aplicación, puedes implementar diversos esquemas de almacenamiento y
eliminación de la caché.
Otro factor importante es que, con el sistema de cache que definimos para el detalle de los posts, no estamos
evitando la conexión al a base de datos la cual viene implícita en la ruta:
app\Http\Controllers\Dashboard\PostController.php
use Illuminate\Support\Facades\Cache;
***
class BlogController extends Controller
{
public function show(Post $post): String // Post::find(<PK>)
***
Para evitar esto, puedes cambiar el tipo de ruta y hacer la búsqueda de manera manual solamente cuando no
exista la caché:
app\Http\Controllers\Dashboard\PostController.php
use Illuminate\Support\Facades\Cache;
***
class BlogController extends Controller
{
public function show(int $id /*slug*/): String
{
if (Cache::has('post_show_' . $id)) {
return Cache::get('post_show_' . $id);
} else {
$post = Post::with('category')->find($id);
321
$cacheView = view('blog.show', compact('post'))->render();
Cache::put('post_show_' . $post->id, $cacheView);
return $cacheView;
}
// return view('blog.show', compact('post'));
}
}
Finalmente, también podemos emplear la función de ayuda cache() en vez del Facade para todos los métodos
anteriores, por ejemplo:
cache()->has('post_show_'.$post->id);
Tipos de controladores
En Laravel, existen diferentes tipos de controladores disponibles, entre ellos se encuentran:
Archivo
El controlador de tipo archivo guarda el caché en archivos en el sistema de archivos; este es el esquema usado
por defecto en Laravel y el archivo encriptado generado se encuentra en storage/framework/; no requiere
configuraciones adicionales y lo podemos usar tanto en desarrollo como en producción.
CACHE_STORE=file
Base de datos
El controlador de tipo base de datos guarda el caché en una tabla de la base de datos; este tipo requiere
configuraciones adicionales, para generar la tabla:
$ php artisan cache:table
Para la mayoría de los casos, este esquema no es el ideal ya que, con el uso de la caché usualmente se quiere
liberar recursos de la base de datos y usando el driver para la caché de la base de datos, podríamos generar un
cuello de botella.
CACHE_STORE=database
Memcached
El controlador de tipo Memcached almacena el caché en un servidor de caché Memcached; Memcached es un
almacén de datos basado en memoria de alto rendimiento; por lo tanto, para poder usarlo, requiere de instalar un
paquete adicional.
CACHE_STORE=memcached
322
Redis
El controlador de tipo Redis almacena el caché en un motor de base de datos llamado Redis; Redis es una de las
configuraciones más populares en Laravel al ser muy rápido; aunque, requiere de instalación y configuración de
un programa externo al proyecto en Laravel.
CACHE_STORE=redis
Array
El controlador de tipo Array almacena los datos en un array en PHP y no requiere de instalación de programas
adicionales.
CACHE_STORE=array
Estas configuraciones se encuentran en el archivo de:
config\cache.php
'default' => env('CACHE_STORE', 'file'),
/*
|--------------------------------------------------------------------------
| Cache Stores
|--------------------------------------------------------------------------
|
| Here you may define all of the cache "stores" for your application as
| well as their drivers. You may even define multiple stores for the
| same cache driver to group types of items stored in your caches.
|
| Supported drivers: "apc", "array", "database", "file",
| "memcached", "redis", "dynamodb", "octane", "null"
|
*/
***
Y a nivel de las variables de entorno:
.env
CACHE_STORE=file
Cache con Redis
Configurar la base de datos de Redis como sistema de caché en Laravel es una excelente manera de mejorar el
rendimiento de la aplicación, en vez de utilizar la base de datos que es en la mayoría de los casos la operación
323
que queremos evitar (como mostramos en el ejemplo anterior), podemos emplear un excelente motor de base de
datos como Redis, que se caracteriza por su velocidad y también facilidad de instalación.
Instalación de Redis
Si estás usando Laravel Dbngin en MacOS, puedes crear una base de datos preservando la configuración por
defecto:
Figura 15-2: Crear una base de datos con redis
Si empleas Laragon hay que configurar una DLL según la versión que estés empleando; más información en el
foro oficial de Laragon:
https://dev.to/dendihandian/installing-php-redis-extension-on-laragon-2mp3
Debes de descargar la DLL en Windows según la versión que tengas ejecutando, NTS o TS y tu versión de PHP,
en mi caso es NTS:
Figura 15-3: Versión de PHP en Laragon
324
Descargas la DLL desde:
https://pecl.php.net/package/redis
Luego copias la DLL en la versión de PHP que estés ejecutando en Laragon; por ejemplo:
C:\laragon\bin\php\php-8.X.X-nts-Win32-vs16-x64\ext
Y activas la extensión:
Figura 15-4: Activar extensión
En Laragon, ya viene instalado por defecto Redis, anteriormente configuramos la DLL o conector a la base de
datos para ejecutar la base de datos de Redis:
C:\laragon\bin\redis\redis-x6X.X\redis-server.exe
Y verás una ventana como la siguiente:
325
Figura 15-5: Redis ejecutándose
Que indica que Redis se está ejecutando y está lista para emplear; de igual forma, puedes probar el estado de
redis ejecutando:
$ redis-cli
Si ver un mensaje como el siguiente:
Could not connect to Redis at 127.0.0.1:6379: No se puede establecer una conexi¾n ya que el
equipo de destino deneg¾ expresamente dicha conexi¾n.
not connected>
Significa que tienes problemas con la ejecución del motor de base de datos.
También puedes ejecutar:
$ redis-cli
$ 127.0.0.1:6379> ping
Y debes de ver como salida:
PONG
En Linux:
326
$ sudo apt-get install redis php8.3-redis
$ sudo systemctl restart php8.3-fpm.service
Configuraciones adicionales
Puede que sea necesario instalar el paquete de Predis a tu proyecto Laravel mediante Composer:
$ composer require predis/predis
El cual es el cliente o conector para que pueda emplear Redis en Laravel, para emplear Redis para la base de
datos, no debería ser necesario, así que solamente instalarlo en caso de que sea necesario.
En caso de que quieras cambiar algun parametro d configuracion de redis, puedes especificarlo de la siguiente
manera:
.env
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
Indicas las opciones de conexion a Redis (al igual que con cualquier otra base de datos):
config/database.php
'redis' => [
'client' => env('REDIS_CLIENT', 'phpredis'),
'default' => [
'host' => env('REDIS_HOST', '127.0.0.1'),
'password' => env('REDIS_PASSWORD'),
'port' => env('REDIS_PORT', 6379),
'database' => env('REDIS_DB', 0),
],
'cache' => [
'host' => env('REDIS_HOST', '127.0.0.1'),
'password' => env('REDIS_PASSWORD'),
'port' => env('REDIS_PORT', 6379),
'database' => env('REDIS_CACHE_DB', 1),
],
];
config\cache.php
'redis' => [
'driver' => 'redis',
327
'connection' => env('REDIS_CACHE_CONNECTION', 'cache'),
'lock_connection' => env('REDIS_CACHE_LOCK_CONNECTION', 'default'),
],
Finalmente, configuramos el conector para que sea Redis ya sea en el .env:
.env
CACHE_STORE=redis
Y/o archivo de configuración:
config\cache.php
'default' => env('CACHE_STORE', 'redis')
Y eso sería todo, al cambiar de sistema de caché, es independiente a la implementación que vayamos a realizar.
Caché de rutas
También es posible guardar la caché de las rutas para que luzcan tal cual está definida en el momento que se
ejecuta el comando de:
$ php artisan route:cache
Esto es útil cuando se va a usar la aplicación en producción para aprovechar el caché de las rutas de Laravel;
tenga en cuenta que Laravel ahora comparará las rutas con el archivo generado en lugar de los archivos reales
usados para las rutas; por lo tanto, si realiza cambios en las rutas debe de ejecutar el comando de:
$ php artisan route:clear
Para limpiar los archivos de caché para las rutas; aunque, puedes realizar cambios en los controladores
registrados en las rutas y las vistas de los controladores
Caso práctico
Para ver un ejemplo de lo anterior, supongamos que tenemos la siguiente ruta:
routes\web.php
Route::get('/', function () {
return ['Laravel' => app()->version()];
});
Ejecutamos el comando para realizar el caché de las rutas:
$ php artisan route:cache
328
Al ingresar desde el navegador, veremos:
http://larafirststeps.test/
{
"Laravel": "12.0.0"
}
Si cambiamos las rutas y/o el resultado que devuelven las rutas:
routes\web.php
Route::get('/', function () {
return ['Laravel New' => app()->version()];
});
Route::get('/test', function () {
return ['Laravel Test' => app()->version()];
});
E ingresamos nuevamente, veremos el mismo resultado anterior:
http://larafirststeps.test/
{
"Laravel": "12.0.0"
}
Y la nueva ruta devuelve un 404:
http://larafirststeps.test/test
404
Por lo tanto, para reflejar los cambios, debes de hacer una limpieza de los archivos de rutas:
$ php artisan route:clear
Ya con esto, podrás ingresar a la nueva ruta y ruta modificada y ver los nuevos cambios:
http://larafirststeps.test/
{
"Laravel New": "12.0.0"
}
Y
329
http://larafirststeps.test/test
{
"Laravel Test": "12.0.0"
}
Puedes habilitar la caché de las rutas en cualquier momento:
$ php artisan route:cache
Puedes probar realizar cambios en tus controladores y vistas desde las rutas con la caché de las rutas activas:
Route::group(['prefix' => 'blog'], function () {
Route::controller(BlogController::class)->group(function () {
Route::get('/', "index")->name('blog.index');
});
});
Y verás que los cambios que realices en el controlador y vistas serán reflejados normalmente; ya que, la
caché se hace a nivel del archivo de rutas y no de los controladores, componentes y vistas asociados en el
proyecto.
Código fuente del capítulo:
https://github.com/libredesarrollo/book-course-laravel-base-11/releases/tag/v0.8
330
Capítulo 16: Gate y Políticas (Autorización)
En este capítulo vamos a ver una introducción a los Gates (Puerta) en Laravel los cuales permiten manejar la
autorización de los usuarios, es decir, para indicar a cuáles partes del sistema pueden ingresar los usuarios en
base a reglas impuestas.
Autenticación y autorización
Antes de entrar en detalle, tenemos que tener en cuenta dos conceptos que pueden causar confusión, la
autenticación y la autorización.
● La autenticación hace referencia a cuando el usuario da sus credenciales (usuario/contraseña) a nivel del
sistema, es decir, se realiza el login.
● La autorización hace referencia a que es lo que el usuario puede hacer, es decir, colocar límites;
anteriormente vimos cómo hacer este proceso con los roles de administrador y regular, pero, en esta
oportunidad estamos empleando un servicio propio de Laravel conocido como Gate.
Puedes imaginar un Gate como una puerta (de allí su nombre) en la cual, si un usuario tiene acceso a ciertas
partes del sistema, significa que la puerta está abierta, si no tiene acceso, entonces la puerta permanece cerrada
y no podrá acceder a esas partes del sistema.
Aclarado esto, vamos a conocer cómo emplear los Gate en Laravel; un Gate luce de la siguiente manera:
use Illuminate\Support\Facades\Gate;
***
Gate::define('update-post', function ($user, $post) {
return $user->id == $post->user_id;
});
Aquí podemos ver 3 elementos importantes:
1. El uso de un Facade: Illuminate\Support\Facades\Gate.
2. Definir una clave para indicar en pocas palabras qué es lo que va a realizar la operación, en el ejemplo
anterior sería "update-post" que sería para actualizar un post, por lo tanto, lo podemos usar en los
controladores para editar un formulario (Los métodos de edit() y update()).
3. El siguiente elemento corresponde a la regla o reglas que quieras imponer, en el ejemplo anterior
indicamos que el post debe de pertenecer a un usuario, pero, puedes colocar más como por ejemplo
preguntar por el rol del usuario.
Otro punto importante son los argumentos, para poder utilizar la autorización, el usuario debe de estar
autenticado y es por eso que el argumento de usuario siempre está presente y es suministrado internamente por
Laravel; el resto de los argumentos son completamente personalizables y los que definas depende de las reglas
que vayas a establecer.
331
Cambios iniciales
Para poder hacer algunos ejemplos con los Gates, vamos a necesitar hacer algunos cambios en el proyecto, por
ejemplo, agregar una columna de usuario id a la tabla post; para ello, creamos una migración:
$ php artisan make:migration add_user_id_to_posts_table
Definimos la nueva columna:
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
/**
* Run the migrations.
*/
public function up(): void
{
Schema::table('posts', function (Blueprint $table) {
$table->foreignId('user_id')->constrained()
->onDelete('cascade');
});
}
/**
* Reverse the migrations.
*/
public function down(): void
{
Schema::table('posts', function (Blueprint $table) {
$table->dropColumn('user_id');
});
}
};
Y ejecutamos el comando de migración:
$ php artisan migrate:fresh
Este comando borrar todas las tablas y las vuelva a generar, esto lo hacemos ya que, existen posts en la base de
datos y no podemos agregar una nueva columna de tipo foránea (no nula) a posts existentes. También pudieras
eliminar todas las publicaciones de manera manual y con eso, evitas tener que crear usuarios y categorías de
prueba y luego ejecutas el comando de:
332
$ php artisan migrate
Aplicamos los cambios en el modelo:
app\Models\Post.php
class Post extends Model
{
use HasFactory;
protected $fillable = [***, 'user_id'];
***
}
Y en el factory, agregamos el campo de usuario id:
database\factories\PostFactory.php
class PostFactory extends Factory
{
public function definition(): array
{
// Post::truncate();
$name = fake()->sentence;
return [
'title' => $name,
'slug' => str($name)->slug(),
'content' => $this->faker->paragraphs(20, true),
'description' => $this->faker->paragraphs(4, true),
'category_id' => $this->faker->randomElement([1, 2, 3]),
'user_id' => $this->faker->randomElement([1, 2]),
'posted' => $this->faker->randomElement(['yes', 'not']),
'image' => $this->faker->imageUrl()
];
}
}
Y el seeder:
database\seeders\PostSeeder.php
***
class PostSeeder extends Seeder
{
***
public function run(): void
333
{
***
Post::create(
[
***
'user_id' => 1
]
);
}
}
}
Con esto, tenemos listo los cambios iniciales para poder crear nuestra primera regla mediante los gates.
Gate define y allow, métodos claves
Hay dos métodos muy importantes en los Gate, el primero es el de define() para definir el Gate con las reglas tal
cual vimos antes:
app\Providers\AppServiceProvider.php
public function boot(): void
{
***
Gate::define('update-post', function ($user, $post) {
return $user->id == $post->user_id;
});
}
Como puedes ver en el código anterior, los gates están definidos en AppServiceProvider.php.
Y para poder usar el Gate anterior, usamos el método de allows(), ya que el Gate anterior es para prevenir que
los usuarios no pueden modificar posts de otros usuarios, lo usamos en los métodos de edición:
app\Http\Controllers\Dashboard\PostController.php
use Illuminate\Support\Facades\Gate;
***
class PostController extends Controller
{
public function edit(Post $post): View
{
if (!Gate::allows('update-post', $post)) {
return abort(403);
}
***
334
}
public function update(PutRequest $request, Post $post): RedirectResponse
{
if (!Gate::allows('update-post', $post)) {
return abort(403);
}
***
}
}
Y verás que, desde el Dashboard cuando intentes modificar un post que no pertenece a un usuario, aparece un
error 403 que por supuesto puedes personalizar con una redirección o cualquier otra operación.
Políticas para agrupar reglas en base a modelos
Anteriormente vimos cómo podemos crear una regla para poder proteger la edición de los post cuando un
usuario que no es dueño del post intenta modificar el mismo, pero, no es la unica operacion que necesita
protección, ya que, por ejemplo, para eliminar un post, se deben de especificar reglas, para la visualización del
post no sería necesario, pero, pensando en otras entidades, por ejemplo, detalle de un usuario, métodos de
pago, entre otros, puede que sea necesario también proteger la vista de detalle. Con esto en mente, podemos
concluir un par de cosas:
1. La primera es que, todas estas operaciones forman parte de la entidad de post.
2. La segunda es que, puede que quieras agregar reglas a otros modelos, categorias, etiquetas, usuarios,
metodos de pago y un largo etc, por lo tanto, definir todas estas reglas dentro del método de boot() del
AppServiceProvider.php no viene siendo la mejor de las ideas ya que, el método se haría demasiada
larga, difícil de leer y de mantener.
Para estos casos, podemos usar las políticas, que no son más que clases asociadas a un modelo en las cuales
definimos reglas y con esto, podemos agrupar las operaciones comunes.
Por lo tanto, podemos concluir que, si necesitas implementar reglas asociadas a un modelo, las políticas son tu
mejor opción, si son reglas que no forman parte de un modelo, puedes usar los Gates.
Creando una política
En Laravel, contamos con un comando con el cual crear una política:
$ php artisan make:policy PostPolicy
Y también la opción de indicar el modelo:
$ php artisan make:policy PostPolicy --model=Post
Con esto, generará un archivo en donde definimos las reglas de autorización; por ejemplo:
app\Policies\PostPolicy.php
335
<?php
namespace App\Policies;
use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\Response;
class PostPolicy
{
// /**
// * Determine whether the user can view any models.
// */
// public function viewAny(User $user): bool
// {
// //
// }
/**
* Determine whether the user can view the model.
*/
public function view(User $user, Post $post): bool
{
return true;
}
/**
* Determine whether the user can create models.
*/
public function create(User $user): bool
{
return $user->id > 0;
}
/**
* Determine whether the user can update the model.
*/
public function update(User $user, Post $post): bool
{
return $user->id == $post->user_id;
}
/**
* Determine whether the user can delete the model.
*/
336
public function delete(User $user, Post $post): bool
{
return $user->id == $post->user_id;
}
// /**
// * Determine whether the user can restore the model.
// */
// public function restore(User $user, Post $post): bool
// {
// //
// }
// /**
// * Determine whether the user can permanently delete the model.
// */
// public function forceDelete(User $user, Post $post): bool
// {
// //
// }
}
Puedes personalizar las reglas a gusto.
Registrar la política
De forma predeterminada, Laravel descubre las políticas automáticamente siempre que el modelo y la política
sigan las convenciones de nomenclatura estándar de Laravel. Específicamente, las políticas deben estar en la
carpeta de Policies o encima del directorio que contiene sus modelos. Entonces, por ejemplo, los modelos
pueden ubicarse en el directorio app/Models mientras que las políticas pueden ubicarse en la carpeta
app/Policies. En esta situación, Laravel buscará políticas en app/Models/Policies y luego en app/Policies.
Además, el nombre de la política debe coincidir con el nombre del modelo y tener el sufijo de Policies. Entonces,
un modelo de User correspondería a una clase de política UserPolicy.
Usar la política
Para utilizarlos, podemos usar el método de allows() la cual, devuelve un boolean; por ejemplo, si queremos
evaluar el método de update() de la política anterior:
Gate::allows('update', $post)
Es importante notar que no es necesario pasar la política PostPolicy, ya que, la misma es determinada por el
tipo de dato suministrado que en este caso sería el post.
También puedes crear métodos adicionales y remover los parámetros en caso de que no los vayas a usar desde
la política:
337
app\Policies\PostPolicy.php
public function index(/*User $user, Post $post*/): bool
{
return true;
}
app\Http\Controllers\Dashboard\PostController.php
Y lo usamos normalmente, indicando el nombre del método en la política que queramos emplear, por ejemplo:
public function index(): View
{
$posts = Post::paginate(2); // personaliza la paginacion como quieras
if (!Gate::allows('index', $posts[0])) {
abort(403);
}
return view('dashboard.post.index', compact('posts'));
}
O en update y delete:
public function edit(Post $post)
{
if (!Gate::allows('delete', $post)) {
return abort(403);
}
***
}
public function update(PutRequest $request, Post $post)
{
if (!Gate::allows('update', $post)) {
return abort(403);
}
***
}
public function destroy(Post $post)
{
if (!Gate::allows('delete', $post)) {
return abort(403);
}
***
}
338
Respuestas de las políticas
Como mencionamos antes, en las políticas pueden definir multitud de reglas, por lo tanto, dependiendo de lo que
estés administrando puedes crear métodos más complejos que la evaluación anterior, es por eso que resulta muy
útil devolver una respuesta más completa en la cual, puedes introducir desde códigos de respuesta hasta
mensajes; por ejemplo:
app\Policies\PostPolicy.php
public function update(User $user, Post $post): Response
{
return $user->id == $post->user_id ? Response::allow()
: Response::deny('You do not own this post.');
//: Response::denyAsNotFound();
// return $user->id == $post->user_id;
}
Junto con el método de inspect() en vez de la de allows():
Gate::inspect('update', $post)
Y tendremos respuestas como la siguiente en caso de que la condición no fuera aceptada:
Illuminate\Auth\Access\Response {#1289 //
app\Http\Controllers\Dashboard\PostController.php:60
#allowed: false
#message: "You do not own this post."
#code: null
#status: null
}
Y desde el controlador, ahora puedes devolver un mensaje más acorde al usuario que intenta editar la
publicación suministrando el mensaje:
$res = Gate::inspect('update', $post);
if (!$res->allowed()) {
return abort(403, $res->message());
}
Y ahora tendrás por la pantalla:
403 YOU DO NOT OWN THIS POST.
Puedes ver el resto de métodos disponibles en:
vendor\laravel\framework\src\Illuminate\Auth\Access\Response.php
339
Modificar guardado de post
Para poder habilitar nuevamente el método de crear post, debemos de proveer el identificador del usuario, que,
para la implementación de la aplicación, sería el usuario autenticado; así que, se crea la relación desde el modelo
de usuario:
app\Models\User.php
<?php
class User extends Authenticatable
{
***
public function posts()
{
return $this->hasMany(Post::class);
}
}
Y desde el controlador, se accede a la relación del post mediante el usuario autenticado para crear el post; en la
práctica se asigna automáticamente el identificador del usuario al post:
app\Http\Controllers\Dashboard\PostController.php
public function store(StoreRequest $request): RedirectResponse
{
// Post::create($request->all());
$post = new Post($request->validated());
$user = Auth::user();
$user->posts()->save($post);
return to_route("post.index")->with('status', "Registro actualizado.");
}
Con esto, queda habilitada nuevamente la opción de crear publicaciones.
Métodos importantes
En este apartado, veremos algunos métodos interesantes que podemos usar junto con los Gates junto con los
que vimos antes.
Gate::check()
El condicional se ejecuta solamente si el usuario tiene permisos de crear el post, al igual que el método de
allows():
if (Gate::check('create', $post)) {
// El usuario puede crear el post
340
// The user can create the post...
}
Gate::any() y Gate::none()
El condicional se ejecuta solamente si el usuario tiene al menos alguno de los permisos habilitados, en este
ejemplo ya sea el de actualizar o eliminar un post:
if (Gate::any(['update', 'delete'], $post)) {
// El usuario puede eliminar el post
// The user can update or delete the post...
}
El condicional se ejecuta solamente si el usuario no puede ni actualizar o eliminar un post:
if (Gate::none(['update, 'delete'], $post)) {
// El usuario no puede actualizar o remover post
// The user can't update or delete the post...
}
Estos dos métodos los puedes usar ya sea que las reglas se encuentren definidas mediante una Política o los
Gates.
User::can() y User::cannot()
También podemos preguntar por los permisos del usuario directamente desde la instancia del usuario mediante el
método de can():
if (Auth::user()->can('create')) {
//
}
O si usas una Política o el Gate necesita parámetros:
if (Auth::user()->can('create', $post)) {
//
}
O la operación contraria:
if (Auth::user()->cannot('create', $post)) {
//
}
Gate::forUser()
Si quieres preguntar para un usuario en particular que no tiene que ser el usuario autenticado:
if (Gate::forUser($user)->allows('update-post', $post)) {
341
// The user can update the post...
}
O si no tiene los permisos:
if (Gate::forUser($user)->denies('update-post', $post)) {
// The user can't update the post...
}
Gate::allowIf()
Muchas veces queremos realizar alguna validación sin definir un Gate; para ello, podemos usar el método de
allowIf() que permite el acceso si la condición es verdadera, da un mensaje de tipo 403 si la condición no se
cumple; por ejemplo:
Gate::allowIf(fn (User $user) => $user->isAdmin())
El allowIf() se puede emplear en controladores y métodos similares; devuelve, una respuesta como la siguiente:
Illuminate\Auth\Access\Response {#1270
app\Http\Controllers\Dashboard\PostController.php:68
#allowed: true
#message: null
#code: null
#status: null
}
Gate::denyIf()
Si con allowIf() permite el acceso si se cumple la condición, con denyIf() se deniega el acceso si se cumple la
condición:
Gate::denyIf(fn (User $user) => !$user->isAdmin())
Recuerda que debes de usar los métodos del allowIf() o el denyIf() con cautela, ya que son métodos que por
buenas prácticas la lógica de los Gates deben estar definidos en el método de boot() o usando las Políticas;
también, su uso se considera buenas prácticas, en vez de hacer condiciones como las siguientes:
if(Auth::user()->isAdmin()){
abort(403)
}
Si quieres evaluar habilidades o permisos de usuarios, es recomendable usar los Gate ya que, el código gana
legibilidad y a la final seguimos buenas prácticas que facilitan la lectura del código.
Recuerda que esta sintaxis:
fn (User $user) => !$user->isAdmin()
342
Hace referencia a una función de flecha en PHP; por lo tanto, su equivalente en una función clásica de PHP
sería:
function (User $user) {
return !$user->isAdmin()
}
Gate::authorize()
El método de Gate::authorize() realiza dos procesos, intenta autorizar una acción si el usuario tiene el permiso y
en caso de que el usuario no tenga permisos, lanza automáticamente una excepción:
Gate::authorize('update', $post);
before()
El método de before() se ejecutará antes que cualquier otro método en la Política, con esto, es posible realizar
configuraciones comunes para todas las funciones definidas en la Política:
app\Policies\PostPolicy.php
class PostPolicy
{
public function before(User $user, string $ability): bool|null
{
if ($user->isAdmin()) {
return null;
}
return false;
}
***
}
Por ejemplo, si se verifica por la habilidad de create (o cualquier otra definida en la Política de post) se ejecutará
el método anterior.
Volviendo al método de before(), debemos retornar un valor nulo (y no true) cuando pase la validación y
queramos evaluar la habilidad correspondiente; por ejemplo si tuviéramos el siguiente código:
class PostPolicy
{
public function before(User $user, string $ability): bool|null
{
if ($user->isAdmin()) {
return true;
}
343
return false;
}
***
public function update(User $user, Post $post): bool
{
return $user->id == $post->user_id;
}
}
Y accedemos a la edición de un post que no sea del usuario, no se bloquearía el acceso ya que desde el método
de before() habilitamos el acceso al permiso retornando true, lo que hace que evaluar el resto de las habilidades
quede desactivado.
Código fuente del capítulo:
https://github.com/libredesarrollo/book-course-laravel-base-11/releases/tag/v0.9
Capítulo 17: Roles y Permisos (Spatie)
Spatie Laravel-Permission es un paquete para manejar los permisos de los usuarios en base a roles de código
abierto que se utiliza con Laravel; es un paquete que fácil de usar al implementar una estructura tan manejada en
los proyectos hoy en día como viene siendo el de permisos y roles que entraremos más en detalle en el siguiente
apartado.
En esta sección, conoceremos en detalle cómo usar este paquete en un proyecto en Laravel y con esto, poder
proteger recursos de una manera más escalable y modular que indicando simplemente una columna de tipo
enumerado para el role.
344
Roles y permisos
En los sistemas típicos que se requiere proteger los recursos de una aplicación, usualmente se utilizan los roles y
permisos para manejar el acceso controlado a cada uno de los recursos; los roles y permisos son un mecanismo
para controlar y restringir el acceso a diferentes partes de una aplicación web.
Los permisos son acciones específicas que un usuario puede realizar en la aplicación, por ejemplo, "publicar un
nuevo artículo" o "eliminar un comentario". Con Spatie laravel-permission, puedes asociar roles y permisos a
usuarios y verificar si un usuario tiene acceso a una acción específica en la aplicación en función de sus roles y
permisos.
Los roles son una forma de agrupar permisos, por ejemplo, podrías tener un role de "administrador" que tiene
permisos para todas las acciones en tu aplicación, mientras que un role de "usuario" solo tendría permisos para
acciones limitadas.
Para entender lo comentado mediante un ejemplo, en el contexto de una aplicación web, los roles pueden ser por
ejemplo "administrador", “editor" y "lector". Cada role tiene un conjunto diferente de permisos que determina qué
acciones puede realizar.
Para los posts en un role administrador:
● Crear post.
● Actualizar post.
● Eliminar post.
● Detalle/Listado post.
Para las categorías en un role administrador:
● Crear categoría.
● Actualizar categoría.
● Eliminar categoría.
● Detalle/Listado categoría.
Para los posts en un role editor:
● Crear post.
● Actualizar post (solamente las suyas).
● Eliminar post (solamente las suyas).
● Detalle/Listado post.
Para las categorías en un role editor:
● Crear categoría.
● Actualizar categoría (solamente las suyas).
● Eliminar categoría (solamente las suyas).
● Detalle/Listado categoría.
Para los posts en un role lector:
● Detalle/Listado post.
Para las categorías en un role lector:
345
● Detalle/Listado categoría.
Puedes obtener más información en:
https://spatie.be/docs/laravel-permission/v6/introduction
https://laravel-news.com/laravel-gates-policies-guards-explained
Instalación y configuración
La instalación de este paquete viene siendo lo típico en el cual, ejecutamos un comando por composer indicando
el paquete que queremos instalar:
$ composer require spatie/laravel-permission
De manera opcional, se registra el provider:
config/app.php
'providers' => [
***
Spatie\Permission\PermissionServiceProvider::class,
];
Aunque, en las últimas versiones de Laravel no es necesario.
Se publica la migración y el archivo de configuración config/permission.php para poder personalizar el mismo:
$ php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
Se ejecuta la migración:
$ php artisan migrate
Y tendremos una salida como la siguiente:
2023_04_16_125650_create_permission_tables ................... 796ms DONE
Con el comando anterior se crean varias tablas, puedes inspeccionar la migración de los permisos
(***_create_permission_tables) y veras que consta de varias tablas:
● roles
● permissions
● model_has_permissions
● model_has_roles
● role_has_permissions
Para poder usar los permisos desde la entidad de los usuarios, registramos el trait de roles:
346
use Spatie\Permission\Traits\HasRoles;
class User extends Authenticatable
{
use *** HasRoles;
***
}
Seeder: Permisos y roles
Crearemos una migración para crear los permisos y roles, además de en los siguientes apartados realizar
algunas pruebas y conocer para qué funciona cada tabla y el manejo de los permisos y roles en spatie de una
manera práctica:
$ php artisan make:seeder RoleSeeder
En la cual, creamos un par de roles para el administrador y editor y sus permisos:
<?php
namespace Database\Seeders;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
class RoleSeeder extends Seeder
{
public function run(): void
{
$role1 = Role::create(['name' => 'Admin']);
$role2 = Role::create(['name' => 'Editor']);
Permission::create(['name' => 'editor.post.index']);
Permission::create(['name' => 'editor.post.create']);
Permission::create(['name' => 'editor.post.update']);
Permission::create(['name' => 'editor.post.destroy']);
Permission::create(['name' => 'editor.category.index']);
Permission::create(['name' => 'editor.category.create']);
Permission::create(['name' => 'editor.category.update']);
Permission::create(['name' => 'editor.category.destroy']);
}
}
347
Como punto clave, como nombre para los permisos se usa un nombre muy descriptivo como:
editor.post.create
Que indica claramente que es un permiso para los usuarios editores (o superior, en este caso, como el usuario
administrador es superior al editor, podemos colocar que también mantiene esta clase de permisos) para crear
post; la misma lógica se emplea para el resto de los permisos.
Y ejecutamos el seeder:
$ php artisan db:seed --class=RoleSeeder
Con esto, tenemos algunos permisos y roles claves con los cuales podemos empezar a trabajar; puedes
inspeccionar la base de datos y veras que solamente se agregamos registros en las tablas de roles y permisos:
● roles
● permissions
Métodos para asignar: Permisos a roles, roles a permisos y usuarios,
permisos y roles
En este apartado vamos a aprender asignar los permisos a cada entidad que corresponda que pueden ser de 3
tipos:
1. Asignar permisos a roles, este viene siendo el caso más común ya que, al final los roles no son más que
una agrupación de permisos, es decir, un role puede tener de cero a N permisos.
2. Asignar roles a usuarios, un usuario puede tener de cero a N roles, que internamente contienen los
permisos y con esto, controlar el acceso a los módulos de la aplicación.
3. Asignar permisos a usuarios, aunque no es el enfoque ideal, también podemos asignar permisos
directamente a los usuarios.
Veamos estos casos en detalle:
Roles a permisos y/o usuarios
Con un permiso o usuario, podemos gestionar los roles de la siguiente manera:
● removeRole() Remueve un role al usuario/permiso.
● assignRole() Asigna un role al usuario/permiso.
● syncRoles() Para sincronizar los roles, debes de suministrar un array con los roles:
○ El role o roles que no se encuentren establecidos como argumentos de este método, pero se
encuentran asignados al usuario/permiso, serán removidos.
○ El role o roles que se encuentren establecidos como argumentos de este método, pero se
encuentre asignados al usuario/permiso se mantienen y los que no se encontraban asignados al
usuario/permiso se agregaran.
En la práctica, tenemos algo como lo siguiente; para los permisos:
Permission::create(['name' => 'editor.post.index'])->assignRole($role1)
348
$permission->removeRole($role1)
$permission->syncRoles([$role1,$role2])
Para los usuarios:
Auth::user()->assignRole($role1)
$user->removeRole($role1)
User::find(2)->syncRoles([$role1,$role2])
Esta dualidad de métodos comunes entre los permisos y usuarios se debe a que el modelo de los permisos,
también implementa el trait de HasRoles que colocamos anteriormente en el modelo de User.php y con esto,
accesso a los mismos métodos:
vendor\spatie\laravel-permission\src\Models\Permission.php
use Spatie\Permission\Traits\HasRoles;
***
En definitiva, puedes asignar los roles a usuarios o permisos.
Ten en cuenta, que el método de assignRole() la puedes usar para sincronizar o solo un role o varios mediante
la siguiente sintaxis:
$permission->assignRole([$role1, $role2, ... ,$role8]);
O
$permission->assignRole($role1, $role2, ... ,$role8);
Permisos a roles (o roles a permisos)
Como mencionamos antes, los roles contienen los permisos que definen el acceso a los usuarios; si revisamos el
modelo de roles:
vendor\spatie\laravel-permission\src\Models\Role.php
public function permissions(): BelongsToMany{}
Y mediante el trait de:
Spatie\Permission\Traits\HasPermissions;
Veremos que tenemos acceso una serie de métodos equivalentes a los presentados anteriormente:
● revokePermissionTo()
● syncPermissions()
● givePermissionTo()
349
Es decir, las siguientes operaciones son equivalentes; para remover un permiso/rol:
$role->revokePermissionTo($permission);
$permission->removeRole($role);
Para otorgar un permiso/rol:
$role->givePermissionTo($permission);
$permission->assignRole($role);
Para sincronizar un array de permisos/roles:
$role->syncPermissions($permissions);
$permission->syncRoles($roles);
En la práctica, asignar un permiso a un role o un role a un permiso viene siendo exactamente igual ya que, es
una relación de tipo muchos a muchos manejada mediante la tabla role_has_permissions así que, puedes
emplear el esquema que prefieras según la situación en la cual te encuentres.
Ten en cuenta, que el método de givePermissionTo() la puedes usar para sincronizar o solo un permiso o varios
mediante la siguiente sintaxis:
$role->givePermissionTo([$permission1, $permission2, ... ,$permission8]);
O
$role->givePermissionTo($permission1, $permission2, ... ,$permission8);
Caso práctico
En este punto, ya sabemos cómo asignar y revocar permisos y roles, veamos un enfoque más práctico para
conocer en detalle para que funciona cada tabla en la base de datos y su relación con las funciones anteriores.
Al ejecutar la migración anterior, tendremos el siguiente resultado:
Figura 17-1: Listado de roles en la base de datos
Y
350
Figura 17-2: Listado de permisos en la base de datos
Veremos que solamente se llenan las tablas de roles y permissions, permaneciendo el resto de las tablas
creadas por Spatie completamente vacias; también es importante notar los IDs/PKs de cada uno de los registros
anteriores ya que los usaremos para referenciarlos desde Eloquent mediante consultas.
Vamos a realizar algunas pruebas asignando roles a los permisos; para ello, recuerda comentar el código anterior
para evitar generar duplicados (o errores) de los permisos y roles:
database\seeders\RoleSeeder.php
public function run(): void
{
// $role1 = Role::create(['name' => 'Admin']);
// $role2 = Role::create(['name' => 'Editor']);
// Permission::create(['name' => 'editor.post.index']);
// Permission::create(['name' => 'editor.post.create']);
// Permission::create(['name' => 'editor.post.update']);
// Permission::create(['name' => 'editor.post.destroy']);
// Permission::create(['name' => 'editor.category.index']);
// Permission::create(['name' => 'editor.category.create']);
// Permission::create(['name' => 'editor.category.update']);
// Permission::create(['name' => 'editor.category.destroy']);
Permission::find(1)->assignRole(Role::find(1));
Permission::find(1)->assignRole(Role::find(2));
}
Y ejecutamos:
$ php artisan db:seed --class=RoleSeeder
351
Y veremos que la tabla afectada es solamente la de role_has_permissions, ya que, acabamos de asignar
permisos a los roles:
Figura 17-3: Tabla pivote para almacenar los permisos a los roles
Ahora, asignamos el role de administrador a un usuario:
database\seeders\RoleSeeder.php
public function run(): void
{
***
// Permission::find(1)->assignRole(Role::find(1));
// Permission::find(1)->assignRole(Role::find(2));
User::find(1)->assignRole(1);
}
Y veremos que ahora la tabla afectada es la de model_has_roles:
Figura 17-4: Tabla pivote para almacenar los roles a los usuarios
Este tiene que ser el paso final en la asignación de roles y permisos ya que, estos son los que usaremos en el
siguiente apartado para verificar si el usuario (autenticado) tiene permisos de acceso para la gestión de los datos.
Otro punto importante es que, puedes asignar o el role de editor al usuario administrador o asignar los permisos
del editor al role administrador, todo depende de cómo quieras organizar la aplicación; como podemos ver, el uso
de permisos y roles nos ofrece un enfoque realmente práctico y flexible.
Terminemos de asignar los permisos al role de administrador:
public function run(): void
{
***
352
$permission1 = Permission::find(1); //->assignRole($role2);
$permission2 = Permission::find(2); //->assignRole($role2);
$permission3 = Permission::find(3); //->assignRole($role2);
$permission4 = Permission::find(4); //->assignRole($role2);
$permission5 = Permission::find(5); //->assignRole($role2);
$permission6 = Permission::find(6); //->assignRole($role2);
$permission7 = Permission::find(7); //->assignRole($role2);
$permission8 = Permission::find(8); //->assignRole($role2);
$role2->givePermissionTo($permission1, $permission2, $permission3, $permission4,
$permission5, $permission6, $permission7, $permission8);
}
Con esto, deberías de tener en la base de datos los 8 permisos asignados solamente al role de Editor.
Verificar accesos
Con nuestras tablas llenas, lo siguiente que vamos a realizar es empezar a verificar accesos al módulo de
dashboard; para ello, tenemos una serie de métodos que podemos emplear.
Verificar permisos y roles en controladores
Creadas las relaciones entre los permisos, roles y/o usuarios, veamos los métodos principales para poder
verificar si el usuario tiene los permisos y/o roles.
Permisos
Con este método puedes preguntar si el usuario tiene un permiso en particular asignado:
$user->can('editor.post.update'); // (bool)
Mediante los roles, también es posible preguntar si tiene tiene el permiso especificado:
$role->hasPermissionTo('editor.post.update');
Roles
Con este método puedes preguntar si el usuario tiene un role en particular asignado:
$user->hasRole('Editor'); // (bool)
O un listado de roles mediante un array:
Auth::user()->hasRole(['Editor', 'Admin'])
Tenemos otros métodos como para, determinar si un usuario tiene alguno de los roles de una lista:
353
Auth::user()->hasAnyRole(['Editor', 'Test'])
O
$user->hasAnyRole('Editor', 'Test');
Si tiene todos los roles especificados:
$user->hasAllRoles(Role::all());
También puede determinar si un usuario tiene exactamente todos los roles de una lista determinada:
$user->hasExactRoles(Role::all());
En el caso de los usuarios, es importante aclarar que los métodos para verificar permisos se pueden aplicar ya
sea si el usuario tiene indirectamente establecido los permisos mediante el role (o roles) o el usuario tiene los
permisos asignados directamente.
Verificar permisos en vistas
Al igual que desde los controladores y similares, es posible preguntar desde un archivo blade si el usuario tiene
permisos:
@can(<PERMISSION>)
Tiene permisos
@endcan
Puedes obtener más información en el siguiente enlace:
https://spatie.be/docs/laravel-permission/v6/basic-usage/role-permissions
Crear un CRUD de roles
Para poder entender de una mejor manera las relaciones entre todos estos entes, vamos a crear el módulo de
CRUD para los roles y permisos, comencemos con los roles; el CRUD es el sistema típico que creamos en
secciones anteriores para los posts y categorías y, por lo tanto, vamos a mostrar únicamente el código sin dar
muchas explicaciones.
Comencemos creando el controlador junto con sus métodos:
app\Http\Controllers\Dashboard\RoleController.php
<?php
namespace App\Http\Controllers\Dashboard;
use App\Http\Controllers\Controller;
354
use App\Http\Requests\Role\PutRequest;
use App\Http\Requests\Role\StoreRequest;
use Spatie\Permission\Models\Role;
class RoleController extends Controller
{
public function index()
{
$roles = Role::paginate(10);
return view('dashboard/role/index', compact('roles'));
}
public function create()
{
$role = new Role();
return view('dashboard.role.create', compact('role'));
}
public function store(StoreRequest $request)
{
Role::create($request->validated());
return to_route('role.index')->with('status', 'Role created');
}
public function show(Role $role)
{
return view('dashboard/role/show',['role'=> $role]);
}
public function edit(Role $role)
{
return view('dashboard.role.edit', compact('role'));
}
public function update(PutRequest $request, Role $role)
{
$role->update($request->validated());
return to_route('role.index')->with('status', 'Role updated');
}
public function destroy(Role $role)
{
$role->delete();
return to_route('role.index')->with('status', 'Role delete');
}
355
}
Creamos la ruta de tipo CRUD:
routes\web.php
Route::group(['prefix' => 'dashboard', 'middleware' => ['auth']], function () {
Route::get('/', function () {
return view('dashboard');
})->name("dashboard");
Route::resources([
'post' => App\Http\Controllers\Dashboard\PostController::class,
'category' => App\Http\Controllers\Dashboard\CategoryController::class,
'role' => App\Http\Controllers\Dashboard\RoleController::class,
]);
});
Creamos los requests, que, si quieres, pudieras simplificarlo a uno solo:
app\Http\Requests\Role\PutRequest.php
<?php
namespace App\Http\Requests\Role;
use Illuminate\Foundation\Http\FormRequest;
class StoreRequest extends FormRequest
{
/**
* Determine if the user is authorized to make this request.
*
* @return bool
*/
public function authorize(): bool
{
return true;
}
/**
* Get the validation rules that apply to the request.
*
* @return array
*/
public function rules(): array
356
{
return [
"name" => "required|min:3|max:500"
];
}
}
Y
app\Http\Requests\Role\PutRequest.php
<?php
namespace App\Http\Requests\Role;
use Illuminate\Foundation\Http\FormRequest;
class PutRequest extends FormRequest
{
/**
* Determine if the user is authorized to make this request.
*
* @return bool
*/
public function authorize(): bool
{
return true;
}
/**
* Get the validation rules that apply to the request.
*
* @return array
*/
public function rules(): array
{
return [
"name" => "required|min:3|max:500"
];
}
}
Y las vistas:
resources\views\dashboard\role\_form.blade.php
357
@csrf
<label for="">Name</label>
<input class="form-control" type="text" name="name" value="{{ old('name', $role->name) }}">
<button class="btn btn-success mt-3" type="submit">Send</button>
resources\views\dashboard\role\create.blade.php
@extends('dashboard.layout')
@section('content')
<h1>Create role</h1>
@include('dashboard.fragment._errors-form')
<form action="{{ route('role.store') }}" method="post">
@include('dashboard.role._form')
</form>
@endsection
resources\views\dashboard\role\edit.blade.php
@extends('dashboard.layout')
@section('content')
<h1>Update Role: {{ $role->title }}</h1>
@include('dashboard.fragment._errors-form')
<form action="{{ route('role.update',$role->id) }}" method="post">
@method("PATCH")
@include('dashboard.role._form')
</form>
@endsection
resources\views\dashboard\role\index.blade.php
@extends('dashboard.master')
@section('content')
<a class="btn btn-primary my-3" href="{{ route('role.create') }}"
target="blank">Create</a>
358
<table class="table">
<thead>
<tr>
<th>
Id
</th>
<th>
Name
</th>
<th>
Options
</th>
</tr>
</thead>
<tbody>
@foreach ($roles as $r)
<tr>
<td>
{{ $r->id }}
</td>
<td>
{{ $r->name }}
</td>
<td>
<a class="btn btn-success mt-2" href="{{ route('role.show',$r)
}}">Show</a>
<a class="btn btn-success mt-2" href="{{ route('role.edit',$r)
}}">Edit</a>
<form action="{{ route('role.destroy', $r) }}" method="post">
@method('DELETE')
@csrf
<button class="btn btn-danger mt-2"
type="submit">Delete</button>
</form>
</td>
</tr>
@endforeach
</tbody>
</table>
<div class="mt-2"></div>
{{ $roles->links() }}
@endsection
359
resources\views\dashboard\role\show.blade.php
@extends('dashboard.layout')
@section('content')
<h1>{{ $role->name }}</h1>
@endsection
Con esto, tendremos un CRUD básico funcional para los roles.
Crear un CRUD de permisos
Para los permisos, de igual manera, creamos el CRUD:
app\Http\Controllers\Dashboard\PermissionController.php
<?php
namespace App\Http\Controllers\Dashboard;
use App\Http\Controllers\Controller;
use App\Http\Requests\Permission\PutRequest;
use App\Http\Requests\Permission\StoreRequest;
use Spatie\Permission\Models\Permission;
class PermissionController extends Controller
{
public function index()
{
$permissions = Permission::paginate(10);
return view('dashboard/permission/index', compact('permissions'));
}
public function create()
{
$permission = new Permission();
return view('dashboard.permission.create', compact('permission'));
}
public function store(StoreRequest $request)
{
Permission::create($request->validated());
return to_route('permission.index')->with('status', 'Permission created');
}
360
public function show(Permission $permission)
{
return view('dashboard/permission/show',['permission'=> $permission]);
}
public function edit(Permission $permission)
{
return view('dashboard.permission.edit', compact('permission'));
}
public function update(PutRequest $request, Permission $permission)
{
$permission->update($request->validated());
return to_route('permission.index')->with('status', 'Permission updated');
}
public function destroy(Permission $permission)
{
$permission->delete();
return to_route('permission.index')->with('status', 'Permission delete');
}
}
Creamos la ruta de tipo CRUD:
routes\web.php
Route::group(['prefix' => 'dashboard', 'middleware' => ['auth']], function () {
Route::get('/', function () {
return view('dashboard');
})->name("dashboard");
Route::resources([
'post' => App\Http\Controllers\Dashboard\PostController::class,
'category' => App\Http\Controllers\Dashboard\CategoryController::class,
'role' => App\Http\Controllers\Dashboard\RoleController::class,
'permission' => App\Http\Controllers\Dashboard\PermissionController::class,
]);
});
Creamos los requests, que, si quieres, pudieras simplificarlo a uno solo:
app\Http\Requests\Permission\StoreRequest.php
<?php
namespace App\Http\Requests\Permission;
361
use Illuminate\Foundation\Http\FormRequest;
class StoreRequest extends FormRequest
{
/**
* Determine if the user is authorized to make this request.
*
* @return bool
*/
public function authorize(): bool
{
return true;
}
/**
* Get the validation rules that apply to the request.
*
* @return array
*/
public function rules(): array
{
return [
"name" => "required|min:3|max:500"
];
}
}
Y
app\Http\Requests\Permission\PutRequest.php
<?php
namespace App\Http\Requests\Permission;
use Illuminate\Foundation\Http\FormRequest;
class PutRequest extends FormRequest
{
/**
* Determine if the user is authorized to make this request.
*
* @return bool
*/
362
public function authorize(): bool
{
return true;
}
/**
* Get the validation rules that apply to the request.
*
* @return array
*/
public function rules(): array
{
return [
"name" => "required|min:3|max:500"
];
}
}
Y las vistas:
resources\views\dashboard\permission\_form.blade.php
@csrf
<label for="">Name</label>
<input class="form-control" type="text" name="name" value="{{ old('name',
$permission->name) }}">
<button class="btn btn-success mt-3" type="submit">Send</button>
resources\views\dashboard\permission\create.blade.php
@extends('dashboard.layout')
@section('content')
<h1>Create permission</h1>
@include('dashboard.fragment._errors-form')
<form action="{{ route('permission.store') }}" method="post">
@include('dashboard.permission._form')
</form>
@endsection
363
resources\views\dashboard\permission\edit.blade.php
@extends('dashboard.layout')
@section('content')
<h1>Update Permission: {{ $permission->title }}</h1>
@include('dashboard.fragment._errors-form')
<form action="{{ route('permission.update',$permission->id) }}" method="post">
@method("PATCH")
@include('dashboard.permission._form')
</form>
@endsection
resources\views\dashboard\permission\index.blade.php
@extends('dashboard.master')
@section('content')
<a class="btn btn-primary my-3" href="{{ route('permission.create') }}"
target="blank">Create</a>
<table class="table">
<thead>
<tr>
<th>
Id
</th>
<th>
Name
</th>
<th>
Options
</th>
</tr>
</thead>
<tbody>
@foreach ($permissions as $p)
<tr>
<td>
{{ $p->id }}
</td>
<td>
364
{{ $p->name }}
</td>
<td>
<a class="btn btn-success mt-2" href="{{
route('permission.show',$p) }}">Show</a>
<a class="btn btn-success mt-2" href="{{
route('permission.edit',$p) }}">Edit</a>
<form action="{{ route('permission.destroy', $p) }}" method="post">
@method('DELETE')
@csrf
<button class="btn btn-danger mt-2"
type="submit">Delete</button>
</form>
</td>
</tr>
@endforeach
</tbody>
</table>
<div class="mt-2"></div>
{{ $permissions->links() }}
@endsection
resources\views\dashboard\permission\show.blade.php
@extends('dashboard.layout')
@section('content')
<h1>{{ $permission->name }}</h1>
@endsection
Agregar/remover permisos a roles
En este apartado, vamos a implementar las opciones para asignar y remover permisos a roles; para esto,
veremos dos implementaciones, una mediante un formulario HTML y otra mediante peticiones HTTP mediante
Javascript.
Estructura inicial
Vamos a usar la vista de detalle (show) de los roles para realizar la gestión de permisos dado un rol; pero, para
no mezclar implementaciones que no corresponden directamente con el controlador RoleController, vamos a
crear un componente que se encargue de realizar esta asignación/remover los permisos de un rol:
$ php artisan make:component Dashboard/role/permission/Manage
Desde aquí, hacemos la configuración base que corresponde a inicializar el role y los permisos del rol:
365
app\View\Components\Dashboard\role\permission\Manage.php
<?php
namespace App\View\Components\Dashboard\role\permission;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\View\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
class Manage extends Component
{
/**
* Create a new component instance.
*/
public $role;
public function __construct(Role $role)
{
$this->role = $role;
}
/**
* Get the view / contents that represent the component.
*/
public function render(): View|Closure|string
{
return view('components.dashboard.role.permission.manage',['permissionsRole' =>
$this->role->permissions]);
}
}
Que luego mostramos en un listado:
resources\views\components\dashboard\role\permission\manage.blade.php
<div>
<h3>Permission</h3>
<ul>
@foreach ($permissionsRole as $p)
<li>{{$p->name}}</li>
@endforeach
366
</ul>
</div>
Desde la vista de detalle del role, insertamos el componente anterior y pasamos el rol:
resources\views\dashboard\role\show.blade.php
@extends('dashboard.layout')
@section('content')
<h1>{{ $role->name }}</h1>
<x-dashboard.role.permission.manage :role="$role"/>
@endsection
Asignar permisos al role mediante un formulario
Como comentamos anteriormente, queremos que la gestión de los permisos de un role no se realice dentro de
RoleController para evitar colocar demasiadas funciones en un mismo lugar; por tal motivo, haremos esta
gestión desde el componente anterior; creamos un nuevo método el cual se encarga de asignar un permiso a un
role y obtenemos el listado de permisos:
app\View\Components\Dashboard\role\permission\Manage.php
<?php
namespace App\View\Components\Dashboard\role\permission;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\View\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
class Manage extends Component
{
***
public function render(): View|Closure|string
{
return view('components.dashboard.role.permission.manage', [
'permissionsRole' => $this->role->permissions,
'permissions' => Permission::get()]);
}
public function handle(Role $role){
$permission = Permission::findOrFail(request('permission'));
$role->givePermissionTo($permission);
return redirect()->back();
367
}
}
Empleamos el método findOrFail() que devuelve el permiso si existe y si no, una excepción de tipo 404.
Su ruta con nombre:
routes\web.php
Route::group(['prefix' => 'dashboard', 'middleware' => ['auth']], function () {
***
Route::post('role/assign/permission/{role}',[
App\View\Components\Dashboard\role\permission\Manage::class, 'handle'
])->name('role.assign.permission');
});
Y desde la vista del componente, se crea un formulario con un SELECT:
resources\views\components\dashboard\role\permission\manage.blade.php
<div>
<h3>Permission</h3>
<ul>
@foreach ($permissionsRole as $p)
<li>{{$p->name}}</li>
@endforeach
</ul>
<h3>Assign</h3>
<form action="{{ route('role.assign.permission', $role->id) }}" method="post">
@csrf
<select name="permission">
@foreach ($permissions as $p)
<option value="{{$p->id}}">{{$p->name}}</option>
@endforeach
</select>
<button type="submit">Send</button>
</form>
</div>
Con esto, ya tenemos la siguiente pantalla:
368
Figura 17-5: Formulario para asignar permisos a un role.
Para realizar la gestión de los permisos de un role.
Remover un permiso de un role mediante un formulario
La siguiente implementación a realizar es la de poder remover permisos de un rol; para ello, debemos de crear
un formulario en el listado de los permisos del rol:
resources\views\components\dashboard\role\permission\manage.blade.php
<li>
<form action="{{ route('role.delete.permission', $role->id) }}" method="post">
@csrf
@method('delete')
369
<input type="hidden" name="permission" value="{{ $p->id }}">
{{ $p->name }}
<button type="submit">
<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
stroke-width="1.5"
stroke="currentColor" class="w-6 h-6">
<path stroke-linecap="round" stroke-linejoin="round"
d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107
1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0
01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114
1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964
51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
</svg>
</button>
</form>
</li>
El icono SVG lo puedes personalizar a gusto; el mismo forma parte de:
https://heroicons.com/
Y crear el método de remover desde el componente:
app\View\Components\Dashboard\role\permission\Manage.php
public function delete(Role $role)
{
$permission = Permission::find(request('permission'));
$role->revokePermissionTo($permission);
return redirect()->back();
}
Y su ruta:
routes\web.php
Route::group(['prefix' => 'dashboard', 'middleware' => ['auth']], function () {
***
Route::delete('role/delete/permission/{role}',[
App\View\Components\Dashboard\role\permission\Manage::class, 'delete'
])->name('role.delete.permission');
});
Con esto, se tiene implementada la opción de remover permisos de un role.
370
Asignar permisos al role mediante peticiones HTTP mediante JavaScript
Como variación, vamos a hacer la gestión de los permisos mediante JavaScript; específicamente lo que nos
interesa en este apartado en la de poder asignar permisos; con este implementación, evitaremos recargar toda la
página al momento de asignar un permiso si no, mediante JavaScript hacer los cambios en el HTML y enviar la
petición mediante JavaScript; claro está, este es un desarrollo más complejo que el de crear solamente la gestión
de un formulario como iremos viendo en este apartado.
Instalar axios
Axios ya es un paquete que viene instalado con Laravel al momento de crear el proyecto:
package.json
{
***
"devDependencies": {
***
"axios": "...",
}
}
Por lo tanto, no lo tenemos que instalar, basta con referenciar el archivo en donde se está importando axios que
por defecto es el archivo:
resources\js\app.js
Y que, por defecto, también estamos importando desde el layout del dashboard.
En caso de que no tengas axios o no quieras emplear el de app.js, puedes importarlo de manera local mediante
la CDN:
resources\views\components\dashboard\role\permission\manage.blade.php
<div>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<h3>Permissions</h3>
***
https://axios-http.com/docs/intro
Puedes instalarlo también mediante Node si lo piensas usar en más módulos de la aplicación; vamos a
aprovechar el método que ya tenemos para asignar permisos a un rol:
app\View\Components\Dashboard\role\permission\Manage.php
public function handle(Role $role){
$permission = Permission::find(request('permission'));
371
$role->givePermissionTo($permission);
return redirect()->back();
}
Es importante mencionar que dependiendo del tipo de aplicación que quieras crear, puedes definir los métodos
de gestión en una rest api o en un controlador; para el desarrollo que estamos llevando a cabo, en el cual,
solamente nos interesa usar un par de métodos, podemos usar el controlador directamente.
Crear petición por axios
Luego, para que podamos tener una respuesta más acorde según si se está haciendo la petición mediante un
formulario o mediante axios, podemos usar la siguiente condición:
resources\views\components\dashboard\role\permission\manage.blade.php
<h3>Assign</h3>
{{-- <form action="{{ route('role.assign.permission', $role->id) }}" method="post"> --}}
@csrf
***
<button type="button" id="buttonAssignPermission">Send</button>
{{-- </form> --}}
<script>
document.getElementById("buttonAssignPermission").addEventListener('click',function(){
assignPermissionToRol({{ $role->id }});
})
function assignPermissionToRol(roleId) {
axios.post('/dashboard/role/assign/permission/' + roleId, {
'permission': document.querySelector('select[name="permission"]').value
}).then((res) => {
console.log(res)
})
}
</script>
En el script anterior:
1. Comentamos el formulario, ya que vamos a hacer la asignación mediante JavaScript.
2. Creamos un listener de tipo click para el botón.
3. Creamos la petición axios a la URL actual para asignar el permiso; en la URL el role establecido y como
parámetro en el body el permiso seleccionado en el SELECT.
Si recargas la página, deberías de ver el permiso asignado al role.
372
Adaptar el método de asignación de permisos para manejar peticiones por formularios y peticiones
axios
Como puedes analizar de las pruebas del script anterior, que el método de asignación de permisos devuelva una
redirección, no es el mejor tipo de respuesta que podemos manejar al momento de realizar una petición axios; ya
que, usualmente para las peticiones tipo axios se retorna algún contenido con el cual se va a utilizar para algún
propósito; por lo tanto, vamos a realizar una condición para saber si la petición realizada es enviada mediante un
formulario o por axios; para ello, podemos usar el siguiente condicional:
public function handle(Role $role){
$permission = Permission::find(request('permission'));
$role->givePermissionTo($permission);
if (request()->ajax()) {
dd('json');
}else{
dd('form');
}
return redirect()->back();
}
Si es una petición enviada por axios verás por pantalla:
json
O si es por el formulario (descomenta el formulario), verás por pantalla:
form
Ahora, adaptamos la respuesta a lo que necesitamos; en el caso de peticiones tipo axios, nos interesa que
devuelva el permiso via JSON:
public function handle(Role $role){
$permission = Permission::findOrFail(request('permission'));
$role->givePermissionTo($permission);
if(request()->ajax()){
//axios, jquery ajax fetch...
return response()->json($permission);
}else{
//form
return redirect()->back();
}
}
373
Agregar un item (permiso) al listado
El siguiente objetivo es, agregar el permiso al listado HTML mediante JavaScript; lo cual en pocas palabras es,
agregar un contenido HTML mediante JavaScript; para esto, existen varias maneras, ya sea crear elementos
HTML y sus atributos mediante funciones en JavaScript:
var li = document.createElement("li");
li.appendChild(document.createTextNode("Permission"));
li.setAttribute("id", "ID");
O usando el atributo innerHTML; que es la manera más limpia y simple de hacerlo:
document.querySelector('#permissionListRol').innerHTML += <CONTENT-HTML>
La propiedad innerHTML devuelve o establece contenido HTML directamente en los descendientes (elementos
hijos) del elemento.
Comencemos agregando algún identificador al listado de ULs para poder referenciar el UL:
<ul id="permissionListRol">
@foreach ($permissionsRole as $p)
Desde el axios, al momento de recibir el OK del servidor, agregamos el permiso:
<script>
document.getElementById("buttonAssignPermission").addEventListener('click',function(){
assignPermissionToRol({{ $role->id }});
})
function assignPermissionToRol(roleId) {
axios.post('/dashboard/role/assign/permission/' + roleId, {
'permission': document.querySelector('select[name="permission"]').value
}).then((res) => {
document.querySelector('#permissionListRol').innerHTML += `
<li>
<form action="{{ route('role.delete.permission', $role->id) }}" method="post">
<input type="hidden" name="permission" value="${res.data.id}">
${res.data.name}
<button type="submit">
<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
stroke-width="1.5"
stroke="currentColor" class="w-6 h-6">
<path stroke-linecap="round" stroke-linejoin="round"
d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107
1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0
01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114
374
1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964
51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
</svg>
</button>
</form>
</li>`
})
}
</script>
En el código anterior, referenciamos la lista UL con:
document.querySelector('#permissionListRol')
Luego, el atributo innerHTML se emplea para obtener todo el HTML del UL:
document.querySelector('#permissionListRol').innerHTML
'\n <li>\n <form
action="http://larafirststeps.test/dashboard/role/delete/permission/2" method="post">\n
<input type="hidden" name="_token" value="zgvhZMT9JvPPT8bkuWJrpHS7r8TTjavnhb5VKiYb">
<input type="hidden" name="_method" value="delete">\n <input type="hidden"
name="permission" value="1">\n\n editor.post.index\n <button
type="submit">\n <svg xmlns="http://www.w3.org/2000/svg" fill="none"
viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">***</svg>\n
</button>\n </form>\n\n </li>
***
<li>\n <form
action="http://larafirststeps.test/dashboard/role/delete/permission/2" method="post">\n
<input type="hidden" name="permission" value="4">\n editor.post.destroy\n
<button type="submit">\n <svg xmlns="http://www.w3.org/2000/svg"
fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6
h-6">***</svg>\n </button>\n </form>\n\n </li>
Y hacemos una sencilla concatenación de textos (en este caso contenido HTML) al final del listado, que
corresponde a la misma estructura del HTML usado para listar un permiso:
ment.querySelector('#permissionListRol').innerHTML += ***
Ya con esto, al momento de que el usuario seleccione un nuevo permiso, se agrega al listado anterior.
Evitar insertar permisos repetidos en el listado
Según las pruebas que hicimos anteriormente, no es posible (y no tiene sentido) agregar para un role dos o más
veces el mismo permiso; por ejemplo, si ejecutamos el siguiente fragmento de código:
375
$role->givePermissionTo($permission);
$role->givePermissionTo($permission);
En donde $permission corresponde al mismo permiso, solamente será registrado en la base de datos un
permiso y no los dos; pero actualmente al hacer la petición por el SELECT que configuramos anteriormente para
que funcione con JavaScript, si se envía un permiso ya registrado, lo volverá a colocar en el listado:
Figura 17-7: Permiso duplicado en el listado
Que no es lo que queremos; ya que, al recargar la página no aparecerá reflejado de esa manera, si no,
solamente un permiso por rol; para evitar esto, es suficiente colocar una clase compuesta en el ID (u otro como
algún atributo personalizado como vamos a hacer para eliminar un permiso en el siguiente apartado) en donde
aparezca reflejado el permiso registrado:
@foreach ($permissionsRole as $p)
<li class="per_{{ $p->id }}">
***
Y antes de enviar la petición por JavaScript, se verifica si ya existe un LI (permiso) registrado con el permiso
correspondiente:
<script>
function assignPermissionToRol(roleId) {
let perId = document.querySelector('select[name="permission"]').value
if(document.querySelector('.per_'+perId) !== null){
return alert('Permission already assigned')
}
axios.post('/dashboard/role/assign/permission/' + roleId, {
'permission': perId
}).then((res) => {...})
</script>
Con esto, solventamos el problema de los permisos duplicados en el listado.
376
Remover permisos del role seleccionado
El siguiente paso consiste en crear la funcionalidad para remover un permiso del role mediante JavaScript; este
desarrollo, tiene un esquema similar al anterior el cual pasa con detectar el tipo de petición realizada desde el
controlador y devolver una respuesta acorde:
public function delete(Role $role)
{
$permission = Permission::find(request('permission'));
$role->revokePermissionTo($permission);
if(request()->ajax()){
return 'ok';
} else{
return redirect()->back();
}
}
Para este método no es necesario que retorne una data con la cual trabajar (en el método anterior simplemente
devuelve "ok") ya que, solamente interesa que haga la operación; con el código de estado (código HTTP) que es
el 200, es condición suficiente para saber que la operación se realizó exitosamente; el siguiente paso es
comentar el formulario (o lo eliminas):
<li class="per_{{ $p->id }}">
{{-- <form action="{{ route('role.delete.permission', $role->id) }}" method="post">
--}}
@csrf
@method('delete')
<input type="hidden" name="permission" value="{{ $p->id }}">
{{ $p->name }}
<button type="button" data-per-id='{{ $p->id }}'>
***
</button>
{{-- </form> --}}
</li>
También agregamos un atributo personalizado al botón para poder referenciar el botón con el permiso a eliminar:
data-per-id='{{ $p->id }}'
Y el JavaScript con el evento click que invoque a la función anterior:
<script>
function setListenerToDeletePermision() {
// eliminacion permisos
document.querySelectorAll("#permissionListRole button").forEach(b => {
377
b.addEventListener('click', function() {
let perId = b.getAttribute('data-per-id')
axios.delete('{{ route('role.delete.permission', $role->id) }}', {
'permission': perId
}).then((res) => {
// TODO Eliminar permiso de listado
})
})
});
}
setListenerToDeletePermision()
</script>
Como esta vez tenemos un listado de botones a los cuales queremos asociar el evento click (un botón de
eliminar por cada permiso asignado al rol), no podemos asignarlo directamente desde el querySelectorAll() ya
que esta devuelve una lista y no el elemento HTML al cual se le puede agregar el listener, primero se iteran cada
uno de estos elementos/botones y luego se les asocia el evento click, en el cual, se elimina el permiso.
Finalmente, la función de setListenerToDeletePermision() se invoca al cargar la página para establecer los
listeners a los permisos actuales del role.
Un punto importante que tienes que tener en cuenta es que, si al hacer la petición de tipo delete por axios, desde
el controlador no es recibido el identificador del permiso (que está establecido en el body de la función de axios)
en otras palabras, al pasar el permiso desde axios por el body en el controlador al acceder al permiso
(request('permission')) da nulo (y por defecto, borra todos los permisos del rol), puedes cambiar la petición de
tipo delete a post en el axios anterior; con esto la lectura del permiso ya no será nula:
let perId = b.getAttribute('data-per-id')
axios.post('{{ route('role.delete.permission', $role->id) }}', {
***
Y creas la ruta:
routes\web.php
***
Route::delete('role/delete/permission/{role}',[
App\View\Components\Dashboard\role\permission\Manage::class, 'delete'
])->name('role.delete.permission');
Route::post('role/delete/permission/{role}',[
App\View\Components\Dashboard\role\permission\Manage::class, 'delete'
])->name('role.delete.permission');
378
No hay ningún problema en que la ruta de tipo delete y post para eliminar un permiso de un role sean idénticas,
ya que, son de tipos distintos; luego, removemos el LI del listado de permisos:
<script>
axios.post('{{ route('role.delete.permission', $role->id) }}', {
'permission': perId
}).then((res) => {
document.querySelector('.per_' + perId).remove()
})
</script>
También debemos de invocar a la función anterior para que recargue el evento click de eliminar cuando se asigna
un nuevo permiso (se crea un nuevo LI/permiso) y de cambiar el HTML para el apartado de eliminar:
<script>
***
axios.post('/dashboard/role/assign/permission/' + roleId, {
'permission': perId
}).then((res) => {
document.querySelector('#permissionListRol').innerHTML += `
<li class="per_${perId}">
<input type="hidden" name="permission" value="${res.data.id}">
${res.data.name}
<button type="button" data-per-id='${res.data.id}'>
<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
stroke-width="1.5"
stroke="currentColor" class="w-6 h-6">
<path stroke-linecap="round" stroke-linejoin="round"
d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107
1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0
01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114
1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964
51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
</svg>
</button>
</li>`
setListenerToDeletePermision()
})
}
</script>
Crear CRUD para los usuarios
El siguiente paso es duplicar uno de los procesos CRUDs existentes para los usuarios; así que, al ser procesos
que ya hicimos antes, no requieren explicaciones adicionales; creamos el controlador:
379
app\Http\Controllers\Dashboard\UserController.php
<?php
namespace App\Http\Controllers\Dashboard;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\PutRequest;
use App\Http\Requests\User\StoreRequest;
class UserController extends Controller
{
public function index()
{
$users = User::paginate(10);
return view('dashboard/user/index', compact('users'));
}
public function create()
{
$user = new User();
return view('dashboard.user.create', compact('user'));
}
public function store(StoreRequest $request)
{
$data = $request->validated();
User::create([
'name' => $data['name'],
'email' => $data['email'],
'password' => $data['password'],
'rol' => 'admin',
]);
return to_route('user.index')->with('status', 'User created');
}
public function show(User $user)
{
return view('dashboard/user/show',['user'=> $user]);
}
public function edit(User $user)
{
return view('dashboard.user.edit', compact('user'));
380
}
public function update(PutRequest $request, User $user)
{
$user->update($request->validated());
return to_route('user.index')->with('status', 'User updated');
}
public function destroy(User $user)
{
$user->delete();
return to_route('user.index')->with('status', 'User delete');
}
}
Para convertir el password en texto plano a hash, tenemos dos formas, podemos hacerlo desde el controlador:
use Illuminate\Support\Facades\Hash;
***
public function store(StoreUserPost $request)
{
User::create(
[
'name' => $request['name'],
'rol' => 'admin',
'surname' => $request['surname'],
'email' => $request['email'],
'password' => Hash::make($request['password']),
]
);
return back()->with('status', 'User created successfully');
}
O mediante un mutador, que no es más que un método que se invoca automáticamente al momento de
establecer un dato sobre el atributo, en este ejemplo, seria al momento de insertar la data validada en el usuario
cuando se crea el usuario:
app\Models\User.php
use Illuminate\Support\Facades\Hash;
***
class User extends Authenticatable
{
381
***
public function setPasswordAttribute($value) {
$this->attributes['password'] = Hash::make($value);
}
}
En el libro, usamos esta segunda opción.
Aquí en el controlador lo único importante es el uso de Hash::make() para convertir la contraseña a un hash para
registrar en la base de datos; ya que, la contraseña debe ser un hash para que la podamos usar para hacer el
login.
Los requests:
app\Http\Requests\User\PutRequest.php
<?php
namespace App\Http\Requests\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;
class PutRequest extends FormRequest
{
/**
* Determine if the user is authorized to make this request.
*
* @return bool
*/
public function authorize(): bool
{
return true;
}
/**
* Get the validation rules that apply to the request.
*
* @return array
*/
public function rules(): array
{
return [
"name" => "required|min:3|max:500",
"email" =>
"required|min:3|max:500|email|unique:users,email,".$this->route("user")->id,
382
"password" => ["required","confirmed", Rules\Password::defaults()::
min(8) // tamano minimo, de 8 en este ejemplo
->letters() // debe de contener al menos una letra
->mixedCase() // debe de contener al menos una letra en mayuscula
->numbers() // debe de contener al menos un numero
->symbols() // debe de contener al menos un caracter especial, como un @
->uncompromised(),// evita que el usuario coloque contrasenas con posibles
fugas como Test@123 o Password@123
],
"password_confirmation" => 'required'
];
}
}
Y
app\Http\Requests\User\StoreRequest.php
<?php
namespace App\Http\Requests\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules;
class StoreRequest extends FormRequest
{
/**
* Determine if the user is authorized to make this request.
*
* @return bool
*/
public function authorize(): bool
{
return true;
}
/**
* Get the validation rules that apply to the request.
*
* @return array
*/
public function rules(): array
{
383
return [
"name" => "required|min:3|max:500",
"email" => "required|min:3|max:500|email|unique:users",
"password" => "required|min:3|max:500",
"password" => ["required","confirmed",Rules\Password::default()
::min(8)
->letters()
->mixedCase()
->numbers()
->symbols()
->uncompromised()],
"password_confirmation" => 'required'
];
}
}
En el caso de los requests agregamos confirmación de contraseña usando:
'password' => '*** confirmed',
Y para indicar por cual campo debemos de confirmar, seguimos la siguiente regla:
<CAMPO>_confirmation
En el caso del ejemplo anterior, sería el siguiente:
"password_confirmation"
Y validaciones sobre la contraseña usando el servicio de Rules:
use Illuminate\Validation\Rules;
En caso de que para la contrasena quieras emplear las reglas básicas junto con la del servicio de Rules, puedes
ver un posible esquema en el request de Store, empleando un array:
["required",Rules\Password::default()
::min(8)
->letters()
->mixedCase()
->numbers()
->symbols()
->uncompromised()]
];
Puedes personalizar las contraseñas como mejor consideres; también se agrega una regla para que el email del
usuario sea único:
384
unique:users
Pero, en la edición se agrega una excepción para evitar que la regla de unique tenga un conflicto contra el
mismo usuario que se quiere editar:
unique:users,email,".$this->route("user")->id
En cuanto a las vistas:
resources\views\dashboard\user\_form.blade.php
@csrf
<label for="">Name</label>
<input class="form-control" type="text" name="name" value="{{ old('name', $user->name) }}">
<label for="">Email</label>
<input class="form-control" type="email" name="email" value="{{ old('email', $user->email)
}}">
<label for="">Password</label>
<input class="form-control" type="password" name="password" value="">
<label for="">Password Confirmation</label>
<input class="form-control" type="password" name="password_confirmation" value="">
<button class="btn btn-success mt-3" type="submit">Send</button>
Es importante observar el campo para confirmar la contraseña usada junto con las validaciones anteriores.
resources\views\dashboard\user\edit.blade.php
@extends('dashboard.layout')
@section('content')
<h1>Update User: {{ $user->name }}</h1>
@include('dashboard.fragment._errors-form')
<form action="{{ route('user.update',$user->id) }}" method="post">
@method("PATCH")
@include('dashboard.user._form')
</form>
@endsection
resources\views\dashboard\user\index.blade.php
385
@extends('dashboard.layout')
@section('content')
<a class="my-2 btn btn-success" href="{{ route("user.create") }}">Create</a>
<table class="table mb-3">
<thead>
<tr>
<th>
Name
</th>
<th>
Email
</th>
<th>
Options
</th>
</tr>
</thead>
<tbody>
@foreach ($users as $u)
<tr>
<td>
{{ $u->name }}
</td>
<td>
{{ $u->email }}
</td>
<td>
<a class="btn btn-primary mt-2" href="{{ route("user.edit", $u)
}}">Edit</a>
<a class="btn btn-primary mt-2" href="{{ route("user.show", $u)
}}">Show</a>
<form action="{{ route("user.destroy", $u) }}" method="post">
@method("DELETE")
@csrf
<button class="btn btn-danger mt-2"
type="submit">Delete</button>
</form>
</td>
</tr>
@endforeach
386
</tbody>
</table>
{{ $users->links() }}
@endsection
resources\views\dashboard\user\show.blade.php
@extends('dashboard.layout')
@section('content')
<h1>{{ $user->name }}</h1>
<ul>
<li>{{ $user->email }}</li>
</ul>
@endsection
Y la ruta de tipo CRUD:
Route::group(['prefix' => 'dashboard', 'middleware' => ['auth']], function () {
Route::get('/', function () {
return view('dashboard');
})->name("dashboard");
Route::resources([
***
'user' => App\Http\Controllers\Dashboard\UserController::class,
]);
]);
Con esto, ya completamos el CRUD para el usuario.
Generar factory para usuarios
Generamos algunos usuarios de prueba usando un factory:
$ php artisan make:factory UserFactory
Este factory usualmente existe por lo tanto daría un error como el siguiente:
ERROR Factory already exists.
Que luce como:
database\factories\UserFactory.php
387
<?php
namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
/**
* @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
*/
class UserFactory extends Factory
{
/**
* Define the model's default state.
*
* @return array<string, mixed>
*/
public function definition(): array
{
return [
'name' => fake()->name(),
'email' => fake()->unique()->safeEmail(),
'email_verified_at' => now(),
'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
// password
'remember_token' => Str::random(10),
];
}
/**
* Indicate that the model's email address should be unverified.
*/
public function unverified(): static
{
return $this->state(fn (array $attributes) => [
'email_verified_at' => null,
]);
}
}
Importante notar que se usa la misma contraseña para todos los usuarios (el hash para 'password' sin comillas);
finalmente se registra en el seeder:
database\seeders\DatabaseSeeder.php
class DatabaseSeeder extends Seeder
388
{
/**
* Seed the application's database.
*/
public function run(): void
{
\App\Models\User::factory(10)->create();
}
}
Y ejecutamos con:
$ php artisan db:seed
Esto generará 10 usuarios de prueba de tipo regular con contraseña de: password.
Gestión de roles a usuario
En este apartado, vamos a crear el proceso para asignar permisos a un usuario autenticado; para esto, vamos a
usar la vista de detalle del usuario, pero, para no mezclar funcionalidades, vamos a crear un nuevo componente
que servirá para realizar la gestión de los roles al usuario; básicamente la misma razón por la cual empleamos un
componente en la gestión de roles y permisos; creamos el componente con:
$ php artisan make:component Dashboard/user/role/permission/Manage
Este componente lo usamos en el detalle del usuario, recibe como parámetro el usuario:
resources\views\dashboard\user\show.blade.php
@section('content')
<h1>{{ $user->name }}</h1>
<ul>
<li>{{ $user->email }}</li>
</ul>
<x-dashboard.user.role.permission.manage :user="$user" />
@endsection
Traeremos todos los roles desde la clase componente:
app\View\Components\Dashboard\user\role\permission\Manage.php
<?php
namespace App\View\Components\Dashboard\user\role\permission;
use Closure;
389
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Spatie\Permission\Models\Role;
use App\Models\User;
class Manage extends Component
{
/**
* Create a new component instance.
*/
public $user;
public function __construct(User $user)
{
$this->user = $user;
}
/**
* Get the view / contents that represent the component.
*/
public function render(): View|Closure|string
{
return view('components.dashboard.user.role.permission.manage',[ 'roles' =>
Role::get() ]);
}
}
Y creamos un listado de selección desde la vista del componente:
resources\views\components\dashboard\user\role\permission\manage.blade.php
<h3>Assign</h3>
<div>
<select name="role">
@foreach ($roles as $r)
<option value="{{ $r->id }}">{{ $r->name }}</option>
@endforeach
</select>
</div>
Listado de roles asignados al usuario
En este apartado vamos a listar los roles del usuario; para ello, podemos obtenerlos de la siguiente manera:
390
$user->roles
Dónde roles es la relación:
vendor\spatie\laravel-permission\src\Traits\HasRoles.php
public function roles(): BelongsToMany {***}
$user, al ser una propiedad del componente, no hay necesidad se pasarlo desde el componente a la vista, por lo
tanto, se puede consumir directamente desde la vista:
resources\views\components\dashboard\user\role\permission\manage.blade.php
<div>
<h3>User's Roles</h3>
<ul id="rolesListUser">
@foreach ($user->roles as $r)
<li class="role_{{ $r->id }}">
{{ $r->name }}
<button type="button" data-role-id="{{ $r->id }}">
<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
stroke-width="1.5"
stroke="currentColor" class="w-6 h-6">
<path stroke-linecap="round" stroke-linejoin="round"
d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107
1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0
1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114
1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964
51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"
/>
</svg>
</button>
</li>
@endforeach
</ul>
***
Con esto, ya tenemos el listado de roles asignados al usuario.
Asignar roles
El método encargado para asignar roles al usuario:
app\View\Components\Dashboard\user\role\permission\Manage.php
public function handle(User $user)
{
391
$role = Role::findOrFail(request('role'));
$user->assignRole($role);
if(request()->ajax()){
// axios
return response()->json($role);
}else{
// form
return back();
}
}
Y la ruta:
routes\web.php
Route::group(['prefix' => 'dashboard', 'middleware' => ['auth']], function () {
***
// user's roles
Route::post('user/assign/role/{user}',[
App\View\Components\Dashboard\user\role\permission\Manage::class, 'handle'
])->name('user.assign.role');
});
Desde la vista, usamos un JavaScript similar al usado para asignar los permisos a roles:
resources\views\components\dashboard\user\role\permission\manage.blade.php
<script>
document.getElementById('buttonAssignRole').addEventListener('click', function() {
assignRoleToUser()
})
function assignRoleToUser() {
const roleId = document.querySelector('select[name="role"]').value
if (document.querySelector('.role_' + roleId) !== null) {
return alert('Role already assigned')
}
axios.post('{{ route('user.assign.role', $user->id) }}', {
'role': roleId
}).then((res) => {
document.querySelector('#rolesListUser').innerHTML += `
<li class="role_${ res.data.id }">
${res.data.name}
392
<button type="button" data-role-id="${ res.data.id }">
<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
stroke-width="1.5"
stroke="currentColor" class="w-6 h-6">
<path stroke-linecap="round" stroke-linejoin="round"
d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107
1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0
1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114
1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964
51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"
/>
</svg>
</button>
</li>
`
})
}
</script>
Eliminar roles
El método encargado para remover roles al usuario:
app\View\Components\Dashboard\user\role\permission\Manage.php
public function delete(User $user){
$role = Role::find(request('role'));
$user->removeRole($role);
if(request()->ajax()){
// axios
return 'ok';
}else{
// form
return back();
}
}
Y la ruta:
routes\web.php
Route::group(['prefix' => 'dashboard', 'middleware' => ['auth']], function () {
***
// usuarios roles
393
Route::delete('user/delete/role/{user}',[
App\View\Components\Dashboard\user\role\permission\Manage::class, 'delete'
])->name('user.delete.role');
Route::post('user/delete/role/{user}',[
App\View\Components\Dashboard\user\role\permission\Manage::class, 'delete'
])->name('user.delete.role');
});
Desde la vista, usamos un JavaScript similar al usado para remover los permisos a roles:
resources\views\components\dashboard\user\role\permission\manage.blade.php
<script>
document.getElementById("buttonAssignRole").addEventListener('click', function(){
assignRolToUser()
})
function assignRolToUser(){
***
`
setListenerToDeleteRole()
})
}
</script>
<script>
function setListenerToDeleteRole(){
document.querySelectorAll("#rolesListUser button").forEach(b => {
b.addEventListener('click', function(){
let roleId = b.getAttribute('data-rol-id')
axios.post("{{route('user.delete.role',$user->id)}}",{
'role': roleId
}).then((res)=> {
// eliminar li
document.querySelector('.role_' + roleId).remove()
})
})
});
}
setListenerToDeleteRole()
</script>
394
Gestión de permisos a usuario
Como comentamos y probamos desde el seeder para manejar los roles, podemos asignar permisos a los
usuarios de manera directa, lo que resulta útil cuando es necesario tener usuarios con una permisología muy
específica en un usuario o pocos usuarios y no queremos crear roles adicionales; vamos a crear las
funcionalidades necesarias para asignar/remover permisos a un usuario.
Para que tenga sentido semántico, vamos a cambiar los nombres de las funciones encargadas de asignar y
remover roles:
app\View\Components\Dashboard\user\role\permission\Manage.php
public function handleRole(User $user){***}
public function deleteRole(User $user){***}
Esto lo hacemos ya que en este mismo componente se definirán la gestión de los permisos; también las rutas:
routes\web.php
Route::post('user/assign/role/{user}',[
App\View\Components\Dashboard\user\role\permission\Manage::class, 'handleRole'
])->name('user.assign.role');
Route::delete('user/delete/role/{user}',[
App\View\Components\Dashboard\user\role\permission\Manage::class, 'deleteRole'
])->name('user.delete.role');
Route::post('user/delete/role/{user}',[
App\View\Components\Dashboard\user\role\permission\Manage::class, 'deleteRole'
])->name('user.delete.role');
Y en la vista:
resources\views\components\dashboard\user\role\permission\manage.blade.php
<h3>Assign Role</h3>
Listado de permisos
En la vista, iteramos los permisos del usuario al igual que hacíamos con los roles:
resources\views\components\dashboard\user\role\permission\manage.blade.php
<h3>User's Permissions</h3>
<ul id="permissionsListUser">
@foreach ($user->permissions as $p)
<li class='permission_{{ $p->id }}'>
{{ $p->name }}
<button data-permission-id='{{ $p->id }}'>
395
<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
stroke-width="1.5"
stroke="currentColor" class="w-6 h-6">
<path stroke-linecap="round" stroke-linejoin="round"
d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107
1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0
01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114
1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964
51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
</svg>
</button>
</li>
@endforeach
</ul>
Asignar permisos a usuario
Este proceso será similar a los anteriores; en esta oportunidad, vamos a reutilizar el componente existente para
manejar los roles y permisos del usuario, aunque si lo prefieres, puedes crear otro componente en el cual realices
la gestión.
Crearemos un nuevo método para asignar permisos al usuario:
app\View\Components\Dashboard\user\role\permission\Manage.php
public function handlePermission(User $user)
{
$permission = Permission::find(request('permission'));
$user->givePermissionTo($permission);
if(request()->ajax()){
// axios
return response()->json($permission);
}else{
// form
return back();
}
}
El HTML y JavaScript para asignar permisos al usuario:
resources\views\components\dashboard\user\role\permission\manage.blade.php
<h3>Assign Permission</h3>
<select name="permission">
396
@foreach ($permissions as $p)
<option value="{{ $p->id }}">{{ $p->name }}</option>
@endforeach
</select>
<button id="buttonAssignPermission">Send</button>
***
{{-- PERMISSION MANAGE --}}
<script>
document.getElementById("buttonAssignPermission").addEventListener('click', function(){
assignPermissionToUser()
})
function assignPermissionToUser(){
let permissionId = document.querySelector('select[name="permission"]').value
if(document.querySelector('.permission_'+perId) !== null){
return alert('Permission already assigned')
}
axios.post("{{ route('user.assign.permission',$user->id ) }}", {
'permission': permissionId
}).then((res)=>{
document.getElementById("permissionsListUser").innerHTML += `
<li class='permission_${res.data.id}'>
${res.data.name }
<button data-permission-id='${res.data.id }'>
<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
stroke-width="1.5"
stroke="currentColor" class="w-6 h-6">
<path stroke-linecap="round" stroke-linejoin="round"
d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107
1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0
01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114
1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964
51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
</svg>
</button>
</li>
`
setListenerToDeletePermission()
})
}
</script>
Y la ruta:
397
routes\web.php
Route::post('user/assign/permission/{user}',[
App\View\Components\Dashboard\user\role\permission\Manage::class, 'handlePermission'
])->name('user.assign.permission');
Retornamos todos los permisos desde el componente:
app\View\Components\Dashboard\user\role\permission\Manage.php
class Manage extends Component
{
***
public function render(): View|Closure|string
{
return view('components.dashboard.user.role.permission.manage', ['roles' =>
Role::get(), 'permissions' => Permission::get()]);
}
***
}
Remover permisos a usuario
Para remover, exactamente el mismo proceso, creamos el método:
app\View\Components\Dashboard\user\role\permission\Manage.php
public function deletePermission(User $user){
$permission = Permission::find(request('permission'));
$user->revokePermissionTo($permission);
if(request()->ajax()){
// axios
return 'ok';
}else{
// form
return back();
}
}
Las rutas:
routes\web.php
398
Route::delete('user/delete/permission/{user}',[
App\View\Components\Dashboard\user\role\permission\Manage::class, 'deletePermission'
])->name('user.delete.permission');
Route::post('user/delete/permission/{user}',[
App\View\Components\Dashboard\user\role\permission\Manage::class, 'deletePermission'
])->name('user.delete.permission');
Y desde la vista:
resources\views\components\dashboard\user\role\permission\manage.blade.php
<script>
function setListenerToDeletePermission(){
document.querySelectorAll("#permissionsListUser button").forEach(b => {
b.addEventListener('click', function(){
let permissionId = b.getAttribute('data-permission-id')
axios.post("{{route('user.delete.permission',$user->id)}}",{
'permission': permissionId
}).then((res)=> {
// eliminar li
document.querySelector('.permission_' + permissionId).remove()
})
})
});
}
setListenerToDeletePermission()
</script>
Verificar accesos mediante spatie
Con todos los procesos CRUDs terminados para realizar la gestión de usuarios, roles y permisos, es momento de
trabajar en otro factor muy importante que es el de definir los permisos para los usuarios los cuales cambian de
módulo en módulo ya que, hay procesos CRUD que solamente podrán gestionarlos los usuarios administradores
como lo es la gestión de los usuarios, que solamente podrán hacerlo los usuarios de tipo administrador, pero,
para la gestión de categorías y posts se podrá realizar tanto por un usuario administrador como editor; esto es
por dar un ejemplo de los cambios que se van a implementar pero, veremos en detalle el proceso en este
apartado.
Recuerda que puedes verificar accesos verificando el permiso:
Auth::user()->hasPermissionTo(<PERMISSION>)
O por roles:
Auth::user()->hasRole(<ROLE>)
399
En este apartado vamos a verificar por permisos que es el enfoque con el cual podemos personalizar más la
experiencia ya que la podemos cambiar y adaptar para cada uno de los métodos de los CRUD.
Crud de posts y categorías
Comencemos con el proceso más sencillo que sería verificar los accesos para los CRUDs para las categorías y
posts; esto pasa simplemente por verificar el acceso a los permisos que ya creamos para cada método:
app\Http\Controllers\Dashboard\PostController.php
<?php
namespace App\Http\Controllers\Dashboard;
***
use Illuminate\Support\Facades\Auth;
class PostController extends Controller
{
public function index(): View
{
if(!Auth::user()->hasPermissionTo('editor.post.index')){
return abort(403);
}
***
}
public function create(): View
{
if(!Auth::user()->hasPermissionTo('editor.post.create')){
return abort(403);
}
***
}
public function store(StoreRequest $request): RedirectResponse
{
if(!Auth::user()->hasPermissionTo('editor.post.create')){
return abort(403);
}
***
}
public function show(Post $post): View
{
if(!Auth::user()->hasPermissionTo('editor.post.index')){
400
return abort(403);
}
***
}
public function edit(Post $post): View
{
if(!Auth::user()->hasPermissionTo('editor.post.update')){
return abort(403);
}
***
}
public function update(PutRequest $request, Post $post): RedirectResponse
{
if(!Auth::user()->hasPermissionTo('editor.post.update')){
return abort(403);
}
***
}
public function destroy(Post $post): RedirectResponse
{
if(!Auth::user()->hasPermissionTo('editor.post.destroy')){
return abort(403);
}
***
}
}
Para el caso de los posts, podemos ver que en la mayoría de los casos tenemos una ambigüedad con los Gate,
ya que, realizan verificaciones de acceso similares; en los únicos métodos que se compenetran sería en el caso
de editar y eliminar (edit(), update() y destroy()) ya que, a nivel de los Gate se verifica que el post pertenezca al
usuario antes de poder eliminar/actualizar el post. Para las categorías quedan como:
<?php
use Illuminate\Support\Facades\Auth;
***
class CategoryController extends Controller
{
public function index()
{
if(!Auth::user()->hasPermissionTo('editor.category.index')){
401
return abort(403);
}
***
}
public function create()
{
if(!Auth::user()->hasPermissionTo('editor.category.create')){
return abort(403);
}
***
}
public function store(StoreRequest $request)
{
if(!Auth::user()->hasPermissionTo('editor.category.create')){
return abort(403);
}
***
}
public function show(Category $category)
{
if(!Auth::user()->hasPermissionTo('editor.category.index')){
return abort(403);
}
***
}
public function edit(Category $category)
{
if(!Auth::user()->hasPermissionTo('editor.category.update')){
return abort(403);
}
***
}
public function update(PutRequest $request, Category $category)
if(!Auth::user()->hasPermissionTo('editor.category.update')){
return abort(403);
}
***
}
402
public function destroy(Category $category)
{
if(!Auth::user()->hasPermissionTo('editor.category.destroy')){
return abort(403);
}
***
}
}
Recuerda remover las verificaciones que hicimos antes mediante los Gate y Políticas ya que no serían
necesarios al emplear Spatie; para la verificación de los permisos por Spatie, no necesariamente debes de
colocarlos en el controlador, puedes migrarlos a las Políticas y/o Gates, crear un controlador base para hacer
estas verificaciones, colocarlas en las clases request al momento de autorizar:
app\Http\Requests\Post\StoreRequest.php
class StoreRequest extends FormRequest
{
public function authorize(): bool
{
return auth()->user()->hasPermissionTo('editor.post.create');
}
}
O verificar directamente por los roles, entre otros posibles esquemas, dependen de ti y de los lineamientos del
proyecto que estés llevando a cabo, en este apartado, solamente vimos una posible implementación, también
recuerda que vimos más métodos de verificación de roles y permisos al inicio del capítulo que también puedes
emplear.
Desde las vistas de index para las categorías y posts, también ocultamos y mostramos los botones de acción
según si tiene el permiso o no; con esto, si por ejemplo no tiene permisos para crear un post, no aparecería el
botón desde la vista; para los posts queda como:
resources\views\dashboard\post\index.blade.php
@extends('dashboard.layout')
@section('content')
@can('editor.post.create')
<a class="btn btn-success my-3" href="{{ route('post.create') }}">Crear</a>
@endcan
***
<td>
@can('editor.post.update')
<a class="mt-2 btn btn-primary" href="{{ route('post.edit', $p)
}}">Editar</a>
@endcan
403
<a class="mt-2 btn btn-primary" href="{{ route('post.show', $p)
}}">Ver</a>
@can('editor.post.destroy')
<form action="{{ route('post.destroy', $p) }}" method="post">
@method('DELETE')
@csrf
<button class="mt-2 btn btn-danger"
type="submit">Eliminar</button>
</form>
@endcan
</td>
</tr>
@endforeach
</tbody>
</table>
{{ $posts->links() }}
@endsection
Para las categorías:
resources\views\dashboard\category\index.blade.php
@extends('dashboard.layout')
@section('content')
@can('editor.category.create')
<a class="my-2 btn btn-success" href="{{ route('category.create') }}">Crear</a>
@endcan
***
@can('editor.category.update')
<a class="btn btn-primary mt-2" href="{{ route('category.edit',
$c) }}">Editar</a>
@endcan
<a class="btn btn-primary mt-2" href="{{ route('category.show', $c)
}}">Ver</a>
@can('editor.category.destroy')
<form action="{{ route('category.destroy', $c) }}"
method="post">
@method('DELETE')
@csrf
<button class="btn btn-danger mt-2"
type="submit">Eliminar</button>
404
</form>
@endcan
</td>
</tr>
@endforeach
</tbody>
</table>
{{ $categories->links() }}
@endsection
Crud de usuarios
Para el CRUD de los usuarios es un proceso que será un poco más interesante, ya que, para los usuarios con el
role de editor, solamente podrán ver a otros usuarios editores y para los usuarios administradores, podrán ver
todos los usuarios; para esto, implementamos el siguiente código:
app\Http\Controllers\Dashboard\UserController.php
public function index(): View
{
if(!Auth::user()->hasPermissionTo('editor.user.index')){
return abort(403);
}
//$users = User::paginate(10); // personaliza la paginacion como quieras
// $users = User::query();
$users = User::when(!Auth::user()->hasRole('Admin'), function($query, $isAdmin){
return $query->where('rol','regular'); // regular = editor
})->paginate(10);
return view('dashboard.user.index', compact('users'));
}
Este comportamiento lo puedes personalizar, y en este ejemplo lo único que se quiere mostrar es una posible
variante del proceso visto para las categorías y posts.
En el código anterior, usamos el método de when() que permite ejecutar un código asociado a una consulta de
Eloquent si hay una condición exitosa (boolean en true); se ejecuta el método de when() para cuando el usuario
autenticado no sea administrador y filtrar para obtener los usuarios regulares/editores; el método de when()
recibe dos parámetros:
● La condición boolean a evaluar, la cual, si es true, ejecuta el método asociado.
● El método asociado para colocar el o los filtros.
405
Aunque, anteriormente ocultamos los usuarios administradores desde el listado de index, no es suficiente, ya
que, el usuario puede ingresar directamente al detalle, edición entre otras secciones del usuario; por ejemplo, si
tenemos un usuario administrador con ID de 1, es posible acceder al mismo directamente para su gestión para
ambos tipos de usuarios (administrador y regular/editor) con:
http://larafirststeps.test/dashboard/user/1
Para evitar este comportamiento, podemos implementar un Gate, para evaluar estas condiciones,
específicamente verificar si tiene el role de Admin o que el usuario que quiere editar no sea administrador:
app\Providers\AppServiceProvider.php
public function boot(): void
{
***
Gate::define('update-view-user-admin', function ($user, $userParam) {
return $user->hasRole('Admin') || !$userParam->hasRole('Admin');
});
}
En la práctica, si el usuario tiene el rol de Administrador, tiene acceso para editar a todos los usuarios, si el
usuario es un editor, sólo puede editar a los usuarios editores.
Al emplear esta misma condición para todos los casos, entiéndase, para poder gestionar, roles, permisos del
usuario, al igual que su edición, creamos un Gate en vez de una Política; el Gate anterior se emplea en todos los
métodos de gestión del usuario y vistas asociadas:
app\Http\Controllers\Dashboard\UserController.php
class UserController extends Controller
{
public function show(User $user): View
{
//if(Gate::authorize('update-view-user-admin')){
// abort(403);
//}
Gate::authorize('update-view-user-admin');
***
}
public function edit(User $user): View
{
Gate::authorize('update-view-user-admin', $user);
if (!Auth::user()->hasPermissionTo('editor.user.update')) {
return abort(403);
}
***
406
}
public function update(PutRequest $request, User $user): RedirectResponse
{
Gate::authorize('update-view-user-admin', $user);
if (!Auth::user()->hasPermissionTo('editor.user.update')) {
return abort(403);
}
***
}
public function destroy(User $user): RedirectResponse
{
Gate::authorize('update-view-user-admin', $user);
if (!Auth::user()->hasPermissionTo('editor.user.destroy')) {
return abort(403);
}
***
}
}
Se crean los permisos para el usuario (en caso de que ya no los tengas definidos):
editor.user.index
editor.user.create
editor.user.update
editor.user.destroy
De momento, tenemos un híbrido entre Gates y comparaciones en el controlador mediante Spatie, lo ideal es que
migremos toda la lógica de permisos a los Gates, pero, esto lo haremos más adelante.
Para el detalle de los usuarios, haremos un bloqueo desde las vistas para que la parte de la gestión de roles y
permisos solamente se pueda realizar por usuarios con el permiso de actualizar usuarios:
resources\views\dashboard\user\show.blade.php
@extends('dashboard.layout')
@section('content')
***
@can('editor.user.update')
<x-dashboard.user.role.permission.manage :user="$user" />
@endcan
@endsection
Desde la vista de index, se aplican las mismas condiciones que las realizadas para las categorías y posts:
407
resources\views\dashboard\user\index.blade.php
@extends('dashboard.layout')
@section('content')
@can('editor.user.create')
<a class="my-2 btn btn-success" href="{{ route('user.create') }}">Create</a>
@endcan
<table class="table mb-3">
***
<td>
@can('editor.user.update')
<a class="btn btn-primary mt-2" href="{{ route('user.edit', $u)
}}">Edit</a>
@endcan
<a class="btn btn-primary mt-2" href="{{ route('user.show', $u)
}}">Show</a>
@can('editor.user.destroy')
<form action="{{ route('user.destroy', $u) }}" method="post">
@method('DELETE')
@csrf
<button class="btn btn-danger mt-2"
type="submit">Delete</button>
</form>
@endcan
</td>
</tr>
@endforeach
</tbody>
</table>
{{ $users->links() }}
@endsection
Acceso de usuarios al dashboard
Recordemos que actualmente definimos al inicio del libro un esquema de un enum de role para conocer si el
usuario es de tipo administrador:
$this->role == "admin"
O regular/editor:
$this->role == "regular"
Al estar usando Spatie, tenemos una dualidad innecesaria con el sistema de roles, ya que, podemos hacer una
comprobación similar a la anterior usando los roles en su lugar:
408
app\Models\User.php
class User extends Authenticatable
{
***
public function accessDashboard(): bool
{
// return $this->role == "admin" || $this->role == "regular";
return $this->hasRole('Editor');
}
}
Aplicamos los nuevos cambios desde el resto de los accesos, que serían la Política de los posts:
app\Policies\PostPolicy.php
class PostPolicy
{
public function before(User $user, string $ability): bool|null
{
if ($user->accessDashboard()) {
return true;
}
return false;
}
***
}
Y desde el middleware de acceso al sistema:
app\Http\Middleware\UserAccessDashboardMiddleware.php
public function handle(Request $request, Closure $next)
{
if (Auth::user()->accessDashboard())
return $next($request);
return redirect("/");
}
Con esto, logramos unificar las consultas de acceso en un mismo esquema y no sería necesario utilizar el
esquema más simple y limitado que implementamos inicialmente para el proyecto en base a un enum de roles.
Puedes personalizar a gusto el funcionamiento del método de accessDashboard() y preguntar por otros roles
y/o permisos; es importante mencionar que no existe una regla o estándar para decidir si vas a preguntar por los
accesos a nivel de roles (como el caso anterior) o permisos (como aplicamos en los controladores y vista) todo
depende de las funcionalidades que quieras aplicar; como recomendación en general, para los elementos más
409
globales, como los accesos a sistemas anteriores, puedes usar comprobaciones a nivel de roles, como hicimos
con el middleware y Política anterior, y para las operaciones más específicas, como una funcionalidad de un
CRUD, puedes usar los permisos.
Crud de roles y permisos
Al igual que ocurre con los procesos anteriores de gestión de usuarios, categorías y posts, es necesario proteger
el módulo de roles y permisos; en esta implementación, simplemente colocaremos que los usuarios
administradores pueden ingresar en este módulo; pero, recuerda que puedes personalizar la experiencia como
mejor consideres; creamos un Gate:
app\Providers\AppServiceProvider.php
Gate::define('is-admin', function ($user) {
return $user->hasRole('Admin');
});
Protegemos el controlador de permisos:
app\Http\Controllers\Dashboard\PermissionController.php
class PermissionController extends Controller
{
public function index(): View
{
Gate::authorize('is-admin');
***
}
public function create(): View
{
Gate::authorize('is-admin');
***
}
public function store(StoreRequest $request): RedirectResponse
{
Gate::authorize('is-admin');
***
}
public function show(Permission $permission): View
{
Gate::authorize('is-admin');
***
}
410
public function edit(Permission $permission): View
{
Gate::authorize('is-admin');
***
}
public function update(PutRequest $request, Permission $permission): RedirectResponse
{
Gate::authorize('is-admin');
***
}
public function destroy(Permission $permission): RedirectResponse
{
Gate::authorize('is-admin');
***
}
}
Y el controlador de rol:
app\Http\Controllers\Dashboard\RoleController.php
class RoleController extends Controller
{
public function index(): View
{
Gate::authorize('is-admin');
***
}
public function create(): View
{
Gate::authorize('is-admin');
***
}
public function store(StoreRequest $request): RedirectResponse
{
Gate::authorize('is-admin');
***
}
public function show(Role $role): View
{
Gate::authorize('is-admin');
411
***
}
public function edit(Role $role): View
{
Gate::authorize('is-admin');
***
}
public function update(PutRequest $request, Role $role): RedirectResponse
{
Gate::authorize('is-admin');
***
}
public function destroy(Role $role): RedirectResponse
{
Gate::authorize('is-admin');
***
}
}
Al igual que el componente:
app\View\Components\Dashboard\role\permission\Manage.php
***
class Manage extends Component
{
public function handle(Role $role){
Gate::authorize('is-admin');
***
}
public function delete(Role $role)
{
Gate::authorize('is-admin');
***
}
}
Como puedes analizar en el código anterior, empleamos un Gate para evaluar la permisología en base a roles; el
mismo esquema puedes seguir para proteger las categorías y posts, que en vez de colocar el condicional
directamente en el controlador; por ejemplo:
412
app\Http\Controllers\Dashboard\PostController.php
public function create(): View
{
$categories = Category::pluck('id', 'title');
$post = new Post();
if(!Auth::user()->hasPermissionTo('editor.post.create')){
return abort(403);
}
if (!Gate::allows('create', $post)) {
abort(403);
}
return view('dashboard.post.create', compact('categories', 'post'));
}
Puedes usar un Gate para manejar el acceso; ya que, el propósito de los Gate es el de manejar la autorización
(acceso) a los recursos de la aplicación.
Migrar verificación de permisos de controladores a Gate para los usuarios
En el controlador y componente para la gestión de los usuarios, existe una dualidad al momento de verificar el
acceso; vamos a migrar la verificación del permiso; por ejemplo:
app\Http\Controllers\Dashboard\UserController.php
public function edit(User $user): View {
Gate::authorize('update-view-user-admin', $user);
if (!Auth::user()->hasPermissionTo('editor.user.update')) {
return abort(403);
}
***
}
Al Gate existente llamado "update-view-user-admin"; por lo tanto, el Gate anterior va a recibir un nuevo parámetro
que corresponde al nombre del permiso, quedando como:
app\Providers\AppServiceProvider.php
Gate::define('update-view-user-admin', function ($user, $userEdit, $permissionName) {
return ($user->hasRole('Admin') || !$userEdit->hasRole('Admin')) &&
Auth::user()->hasPermissionTo($permissionName);
});
413
Como puedes apreciar, colocamos una condición extra usando el AND para evaluar la condición del permiso para
acceder al recurso; ahora, para pasar dos o más parámetros a un Gate (en nuestro ejemplo sería el permiso y
usuario a editar, usamos un array para indicar los parámetros; por ejemplo:
Gate::authorize('update-view-user-admin', [$user, 'editor.user.index']);
Quedando el controlador como:
app\Http\Controllers\Dashboard\UserController.php
class UserController extends Controller
{
***
public function show(User $user): View
{
Gate::authorize('update-view-user-admin', [$user, 'editor.user.index']);
***
}
public function edit(User $user): View
{
Gate::authorize('update-view-user-admin', [$user,'editor.user.update']);
***
}
public function update(PutRequest $request, User $user): RedirectResponse
{
Gate::authorize('update-view-user-admin', [$user,'editor.user.update']);
***
}
public function destroy(User $user): RedirectResponse
{
Gate::authorize('update-view-user-admin', [$user,'editor.user.destroy']);
***
}
}
Y el componente:
app\View\Components\Dashboard\user\role\permission\Manage.php
class Manage extends Component
{
***
// permissions
public function handlePermission(User $user)
414
{
Gate::authorize('update-view-user-admin', [$user,'editor.user.update']);
***
}
public function deletePermission(User $user){
Gate::authorize('update-view-user-admin', [$user,'editor.user.update']);
***
}
// roles
public function handleRole(User $user)
{
Gate::authorize('update-view-user-admin', [$user,'editor.user.update']);
***
}
public function deleteRole(User $user){
Gate::authorize('update-view-user-admin', [$user,'editor.user.update']);
***
}
}
No es un cambio que debamos de hacer de manera obligada ya que, como puedes apreciar, solamente el
cambio de migrar a Gate es aplicado en las funciones en donde está involucrado un usuario, como el de editor,
detalle y eliminar, quedando el resto de los controladores sin cambio alguno; pero, es un cambio interesante con
el cual podemos evaluar en una sola condición (en un solo Gate) todas las reglas de acceso necesarias para
ingresar a un controlador.
Ahora tenemos una aplicación mucho más modular y entendible; como punto importante, los gates a la final son
permisos, así que la integración con el sistema de roles y permisos de spatie queda perfecta para evaluar estos
escenarios.
Definir enlaces y verificación de accesos a los CRUDs en las vistas
Es momento de ajustar algunos detalles en la aplicación definiendo los enlaces para acceder a los listados de los
nuevos procesos CRUDs:
resources\views\layouts\navigation.blade.php
***
<div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
***
<x-nav-link :href="route('category.index')"
:active="request()->routeIs('category.index')">
{{ __('Category') }}
</x-nav-link>
<x-nav-link :href="route('user.index')" :active="request()->routeIs('user.index')">
415
{{ __('User') }}
</x-nav-link>
<x-nav-link :href="route('role.index')" :active="request()->routeIs('role.index')">
{{ __('Role') }}
</x-nav-link>
<x-nav-link :href="route('permission.index')"
:active="request()->routeIs('permission.index')">
{{ __('Permission') }}
</x-nav-link>
</div>
***
Ahora, colocaremos una condición para indicar si se van a renderizar o no según el nivel de acceso del usuario
autenticado; para esto, usaremos la misma condición empleada en los controladores, en el método
create()/index(); es decir, si para acceder al listado de post y categorías desde el controlador tenemos:
Auth::user()->hasPermissionTo('editor.post.index'))
***
Auth::user()->hasPermissionTo('editor.category.index'))
Desde la vista se emplea la misma verificación:
resources\views\layouts\navigation.blade.php
***
<div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
<x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
{{ __('Dashboard') }}
</x-nav-link>
@can('editor.post.index')
<x-nav-link :href="route('post.index')" :active="request()->routeIs('post.index')">
{{ __('Post') }}
</x-nav-link>
@endcan
@can('editor.category.index')
<x-nav-link :href="route('category.index')"
:active="request()->routeIs('category.index')">
{{ __('Category') }}
</x-nav-link>
@endcan
@can('editor.user.index')
416
<x-nav-link :href="route('user.index')" :active="request()->routeIs('user.index')">
{{ __('User') }}
</x-nav-link>
@endcan
@if (Auth::user()->hasRole('Admin'))
<x-nav-link :href="route('role.index')" :active="request()->routeIs('role.index')">
{{ __('Role') }}
</x-nav-link>
<x-nav-link :href="route('permission.index')"
:active="request()->routeIs('permission.index')">
{{ __('Permission') }}
</x-nav-link>
@endif
</div>
***
Diseño
Ya para terminar este capítulo, vamos a acomodar el diseño para los componentes de gestión creados en este
capítulo; para ello; aplicamos cierto estilo base para crear unos bordes separados con los elementos centrados
para los listados y el CSS clásico usado en los botones; se colocan cartas para los listados y formularios; te
mostramos el resultado de una de las vistas del componente que debes de replicar en la otra vista del
componente que tiene una estructura similar:
resources\views\components\dashboard\user\role\permission\manage.blade.php
<div>
<div class="card card-gray">
<h3>Assign Role</h3>
<div class="ml-3">
<ul id="rolesListUser">
@foreach ($user->roles as $r)
<li class='role_{{ $r->id }} p-2 border border-purple-400 flex
items-center gap-3 mb-1'>
{{ $r->name }}
<button class="btn-sm btn-danger" data-rol-id='{{ $r->id }}'>
***
</button>
</li>
@endforeach
</ul>
</div>
417
<select name="role">
@foreach ($roles as $r)
<option value="{{ $r->id }}">{{ $r->name }}</option>
@endforeach
</select>
<button id="buttonAssignRole" class="btn btn-primary">Send</button>
</div>
<div class="card card-gray mt-5">
<h3>Assign Permission</h3>
<div class="ml-3">
<ul id="permissionsListUser">
@foreach ($user->permissions as $p)
<li class='p-2 border border-purple-400 flex items-center gap-3 mb-1
permission_{{ $p->id }}'>
{{ $p->name }}
<button class="btn-sm btn-danger" data-permission-id='{{ $p->id
}}'>
<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0
0 24 24"
stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
<path stroke-linecap="round" stroke-linejoin="round"
d="M14.74 9l-.346 9m-4.788 0L9.26
9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244
2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0
00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5
0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09
2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
</svg>
</button>
</li>
@endforeach
</ul>
<select name="permission">
@foreach ($permissions as $p)
<option value="{{ $p->id }}">{{ $p->name }}</option>
@endforeach
</select>
<button id="buttonAssignPermission" class="btn btn-primary">Send</button>
</div>
</div>
{{-- ROLES MANAGE --}}
<script>
document.getElementById("buttonAssignRole").addEventListener('click', function() {
418
assignRolToUser()
})
function assignRolToUser() {
***
axios.post("{{ route('user.assign.role', $user->id) }}", {
'role': roleId
}).then((res) => {
document.getElementById("rolesListUser").innerHTML += `
<li class='p-2 border border-purple-400 flex items-center gap-3 mb-1
role_${res.data.id}'>
${res.data.name }
<button class='btn-sm btn-danger' data-rol-id='${res.data.id }'>
<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
stroke-width="1.5"
stroke="currentColor" class="w-6 h-6">
<path stroke-linecap="round" stroke-linejoin="round"
d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107
1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0
01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114
1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964
51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
</svg>
</button>
</li>
`
setListenerToDeleteRole()
})
}
</script>
***
{{-- PERMISSION MANAGE --}}
<script>
document.getElementById("buttonAssignPermission").addEventListener('click',
function() {
assignPermissionToUser()
})
function assignPermissionToUser() {
***
axios.post("{{ route('user.assign.permission', $user->id) }}", {
'permission': permissionId
419
}).then((res) => {
document.getElementById("permissionsListUser").innerHTML += `
<li class='p-2 border border-purple-400 flex items-center gap-3 mb-1
permission_${res.data.id}'>
${res.data.name }
<button class='btn-sm btn-danger' data-permission-id='${res.data.id }'>
***
</button>
</li>
`
setListenerToDeletePermission()
})
}
</script>
***
</div>
Y el CSS generado en la hoja de estilo queda como:
resources\css\app.css
/* ******************** base */
h3{
@apply text-lg mb-3
}
/* ******************** base */
/* ******************** card */
.card-gray {
@apply bg-gray-200
}
/* ******************** card */
Este es un estilo mínimo aplicado, recuerda que puedes personalizarlo a gusto, recuerda también que puedes
adaptar el resto de las vistas que tienen una estructura similar, como la de:
resources\views\components\dashboard\role\permission\manage.blade.php
Código fuente del capítulo:
https://github.com/libredesarrollo/book-course-laravel-base-11/releases/tag/v0.10
420
Capítulo 18: Relaciones en Laravel
En este capítulo, vamos a conocer cómo podemos emplear las relaciones en Laravel; las relaciones de tipo uno a
uno, muchos a muchos y relaciones polimórficas; de momento, hemos empleado las relaciones de tipo uno a
muchos entre las categorías y los posts y también entre los posts y los usuarios.
Relaciones uno a uno
En una relación uno a uno, cada registro de una tabla está asociado con un solo registro, por ejemplo, tenemos la
relación entre un usuario y el perfil.
Definimos el perfil, en el cual indicamos que pertenecen a un usuario:
class Profile extends Model
{
public function user() {
return $this->belongsTo(User::class);
}
}
Esto significa que es en la tabla de perfil la que debemos de almacenar la clave foránea:
$ php artisan make:migration create_profiles_table
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
class CreateProfilesTable extends Migration
{
public function up()
{
Schema::create('profiles', function (Blueprint $table) {
$table->id();
$table->unsignedBigInteger('user_id');
$table->string('avatar');
$table->string('address')->nullable();
$table->timestamps();
$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
// $table->foreignId('user_id')->constrained()->onDelete('cascade');
});
421
}
public function down()
{
Schema::dropIfExists('profiles');
}
}
Para crear una FK, tenemos varias formas en Laravel, pero, la más manual es el siguiente esquema:
$table->unsignedBigInteger('user_id');
$table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
Y con el esquema más moderno, no es necesario definir la columna como en el caso anterior, ya la crea
automáticamente mediante el foreignId():
$table->foreignId('user_id')->constrained()->onDelete('cascade');
Puedes emplear el esquema que prefieras. En el modelo de usuarios, colocamos una relación de tipo hasOne,
que indica, que solamente podemos tener una relación de tipo uno a uno:
class User extends Model
{
public function profile() {
return $this->hasOne(Profile::class);
}
}
Quedando la migración del usuario como viene originalmente definida por Laravel, lo cual es ideal para evitar
modificar un modelo interno al framework.
Agregar un perfil al usuario es tan fácil como lo mostramos en el siguiente código:
$user = User::find(1);
$profile = $user->profile;
$profile = Profile::find(1);
$user = $profile->user;
Relaciones uno a muchos
Este tipo de relaciones las manejamos anteriormente entre los posts y las categorías, al igual que entre los posts
y los usuarios.
En esta relación podemos establecer múltiples registros a un solo registro, un solo usuario puede tener de cero a
múltiples posts, o una categoría puede estar establecidos a múltiples posts:
422
class Category extends Model
{
public function posts() {
return $this->hasMany(Post::class);
}
}
class Post extends Model
{
public function category() {
return $this->belongsTo(Category::class);
}
}
Ya anteriormente vimos múltiples operaciones para administrar este tipo de operaciones, por ejemplo:
$category = Category::find(1);
$posts = $category->posts;
$post = Post::find(1);
$category = $post->category;
Relaciones muchos a muchos
Para las relaciones muchos a muchos es necesario tener una tabla intermedia o pivote para que se pueda
registrar las relaciones entre las entidades, este tipo de esquemas se emplean en cualquier sistema de base de
datos relacionadas así que, no vamos a dar muchos detalles más al respecto.
En el ejemplo que vamos a realizar, es la de etiquetas en donde una etiqueta puede estar asignadas a múltiples
posts y un post pueden estar asignadas a múltiples etiquetas.
Comencemos creando la migración:
$ php artisan make:migration create_post_tag_table
Y el modelo:
php artisan make:model Tag
Con las migraciones generadas, agrega los campos necesarios para cada modelo.
database/migrations/...create_post_tag_table.php
Schema::create('tags', function (Blueprint $table) {
$table->id();
$table->string('name');
423
$table->timestamps();
});
Schema::create('post_tag', function (Blueprint $table) {
$table->id();
$table->unsignedBigInteger('post_id');
$table->unsignedBigInteger('tag_id');
$table->foreign('post_id')->references('id')->on('posts')->onDelete('cascade');
$table->foreign('tag_id')->references('id')->on('tags')->onDelete('cascade');
});
En el caso de la tabla pivote, cuyo nombre recomendado es el de post_tag, se encuentran las claves foráneas
de ambas relaciones, es aconsejable mantener el nombre para evitar que Laravel no encuentre la tabla pivote al
momento de realizar las operaciones de gestión entre ambas entidades.
En el modelo de post, definimos una relación de tipo muchos a muchos:
app/Models/Post.php
class Post extends Model
{
public function tags()
{
return $this->belongsToMany(Tag::class);
}
}
Al igual que en las etiquetas:
app/Models/Tag.php
class Tag extends Model
{
public function posts()
{
return $this->belongsToMany(Post::class);
}
}
A diferencia de las relaciones que vimos antes, ambas relaciones se encuentran especificadas de la misma
manera, es decir, ninguna de ellas conserva la clave relacional de la otra tabla, por lo tanto, el tipo de relación
definida en Laravel no cambia, las FKs de cada una de ellas se encuentran en una tabla pivote, por lo tanto, las
operaciones entre ellas pueden ser un espejo, es decir, las operaciones que hagas en la entidad de posts las
puedes hacer en la entidad de etiquetas.
Para emplearlas:
424
$post = Post::find(1);
$tags = $post->tags;
Obtener posts de una etiqueta:
$tag = Tag::find(1);
$posts = $tag->posts;
En cuanto a los métodos para agregar/remover etiquetas/posts:
En las relaciones muchos a muchos, tenemos 3 métodos principales para poder administrar una colección de
datos en una relación:
1. attach(): Este método se utiliza para crear una nueva relación en la tabla pivote.
a. $post->tags()->attach(1), en el ejemplo anterior, permite establecer la etiqueta con identificador 1
en el post.
2. detach(): Este método elimina la relación entre los dos modelos, es decir, lo elimina de la tabla pivote,
viene siendo el proceso inverso del método attach().
3. sync(): Este método permite sincronizar el listado de identificadores proporcionados, eliminando de la
relación los que no estén presentes en el listado y agregando aquellos que estén presentes en el listado.
Con todos los métodos anteriores, puedes pasarle como parámetro un identificador, la instancia del modelo o un
array con identificadores o instancias de modelos:
$post = Post::find(80)
$tag2 = Tag::find(2)
$tag = Tag::find(1)
$post->tags()->attach($tag)
$tag2->posts()->detach(790)
$tag2->posts()->sync(79)
$tag2->posts()->attach($post)
$tag2->posts()->attach([79,80])
Para probar estas operaciones, te recomiendo que uses Tinker, también recuerda crear algunas etiquetas de
ejemplo ya sea mediante Tinker o directamente en la base de datos.
Seguramente te estás preguntando porque colocamos unos paréntesis delante de la relación:
$tag2->posts()->detach(790)
La razón es que, cuando no los colocamos:
$tag2->posts
= Illuminate\Database\Eloquent\Collection {#6428
all: [
App\Models\Post
425
$tag2->posts()
= Illuminate\Database\Eloquent\Relations\BelongsToMany {#6295
+withTimestamps: false,
}
Nos devuelve directamente una colección, que son los "arrays de PHP" en Laravel que trataremos en otro
apartado, por lo tanto, con estos "arrays" no podemos hacer operaciones a la base de datos mediante Eloquent o
similares, y es precisamente esta es la razón, necesitamos acceder a la relación y para ello, colocamos los
paréntesis:
$tag2->posts()
= Illuminate\Database\Eloquent\Relations\BelongsToMany {#6295
+withTimestamps: false,
}
Estas pruebas las puedes ver claramente en Tinker y esto se aplica a cualquiera de las relaciones que vimos
antes, recauda que en el caso de las relaciones muchos a muchos, las operaciones que vimos antes que
mayormente usamos la relación de etiquetas, se aplican a la relación de post.
Relaciones polimórficas
Anteriormente vimos cómo manejar una relación de tipo muchos a muchos entre las etiquetas y las
publicaciones, las etiquetas son un tipo de relación que los podemos emplear en otros tipos de relaciones y nos
solo con los posts, por ejemplo, si tuviéramos una web de alquileres de habitaciones u hoteles, podemos emplear
un sistema de etiquetas, o de ventas de casas, automóviles, una web de videos como YouTube entre otros, las
etiquetas son una estructura muy común para agregar dados a las entidades principales, por lo tanto, resulta muy
común querer emplear este tipo de relaciones con otros datos y en Laravel lo tenemos muy fácil, en vez de crear
una tabla pivot para cada relación como mostramos anteriormente, podemos crear las relaciones polimórficas
que en otras palabras, permite emplear la misma tabla pivote para cualquier relación que queramos relacionar
con las etiquetas y Laravel de manera interna sabe a que le pertenece cada relación mediante una etiqueta:
Figura 18:1 Campo de tipo para identificar la relación
De esta forma, con esta columna que es gestionada internamente por Laravel, podemos emplear una misma
tabla pivote para mapear distintos tipos como usuarios, videos o publicaciones en nuestros modelos en Laravel
de manera transparente para nosotros.
Otro ejemplo de relación que puede ser de tipo polimórfica es el de documentos/comentarios, que pueden ser de
una persona, publicación, usuario, entre otros este tipo de relación sería de uno a muchos de tipo polimórfica.
426
Anteriormente empleamos una relación polimórfica entre los usuarios y los tokens de autenticación mediante
Sanctum.
Las relaciones polimórficas permiten que un registro en una tabla esté relacionado con múltiples modelos
diferentes.
Las relaciones polimórficas en Laravel Eloquent son una herramienta poderosa para manejar situaciones en las
que un registro puede estar relacionado con diferentes entidades. En lugar de crear tablas separadas para cada
tipo de relación, las relaciones polimórficas nos permiten establecer conexiones flexibles entre modelos. Aquí
tienes una introducción con ejemplos:
A diferencia de las relaciones tradicionales (como 1 a n o n a n), donde la relación es siempre fija, en las
relaciones polimórficas, la relación puede variar según el registro.
Aunque comenzamos introduciendo el uso de las relaciones polimorfismo para las relaciones de tipo muchos a
muchos, también las podemos emplear en el resto de las relaciones, pero, es en el uso de las relaciones de tipo
muchos a muchos que tiene principal importancia (y también las de uno a muchos).
Es importante notar que para la relación principal, la de etiquetas se emplea el método de morphToMany() para
definir la relación, y para la "etiquetable" se emplea morphedByMany(), es decir, esta última sería la que tiene la
relación polimórfica.
Ahora, para todos los tipos de relaciones polimorfismo, se emplea el prefijo de morph para definir las mismas:
● morphOne(MODEL, PIVOTTABLE): Define una relación uno a uno polimórfica. Por ejemplo, una relación
para el perfil puede emplearse para distintos tipos como usuarios, personas o empresas, su equivalente a
las relaciones clásicas viene siendo el de tipo hasOne().
● morphMany(MODEL, PIVOTTABLE): Se utiliza para definir una relación uno a muchos polimórfica. Por
ejemplo, una relación de categorías puede estar relacionada con otros modelos como posts o vídeos, su
equivalente a las relaciones clásicas vienen siendo hasMany().
● ManyToMany:
○ morphToMany(MODEL, PIVOTTABLE): Este método se utiliza para definir una relación muchos a
muchos polimórfica. Por ejemplo, la tabla tags que puede estar relacionada con diferentes
tipos de modelos como posts o vídeos.
○ morphedByMany(MODEL, PIVOTTABLE): Este método se utiliza en un modelo para establecer
una relación de muchos a muchos con otros modelos utilizando una relación polimórfica, esta
relación se coloca al "etiquetable".
La definición de estos métodos puede ser un poco abstractos, así que veamos algunos ejemplos.
Caso práctico: Relación muchos a muchos
Comentemos creando las migraciones:
$ php artisan make:migration create_tags_table
<?php
427
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
class CreateTagsTable extends Migration
{
public function up()
{
Schema::create('tags', function (Blueprint $table) {
$table->id();
$table->string('title');
$table->timestamps();
});
}
public function down()
{
Schema::dropIfExists('tags');
}
}
Otra migración para la tabla pivote:
$ php artisan make:migration create_taggables_table
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
class CreateTaggablesTable extends Migration
{
public function up()
{
Schema::create('taggables', function (Blueprint $table) {
$table->id();
$table->unsignedBigInteger('tag_id');
$table->unsignedBigInteger('taggable_id');
$table->string('taggable_type'); // 'App\Models\Post'
$table->timestamps();
$table->unique(['tag_id', 'taggable_id', 'taggable_type']);
$table->foreign('tag_id')->references('id')->on('tags')->onDelete('cascade');
});
428
}
public function down()
{
Schema::dropIfExists('taggables');
}
}
Que usualmente se le coloca el sufijo de able como en el caso anterior.
Y en los modelos:
// app/Models/Tag.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Tag extends Model
{
public function posts()
{
return $this->morphedByMany(Post::class, 'taggable');
}
}
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Post extends Model
{
public function tags()
{
return $this->morphToMany(Tag::class, 'taggable');
}
}
En el controlador de posts, podemos realizar algunas modificaciones para la asignación de etiquetas a los posts:
app\Http\Controllers\Dashboard\PostController.php
class PostController extends Controller
{
public function create()
{
429
$tags = Tag::pluck('id', 'title');
$categories = Category::pluck('id', 'title');
$post = new Post();
return view('dashboard.post.create', compact('post', 'categories', 'tags'));
}
public function store(StorePostPost $request)
{
$post = Post::create($requestData);
$post->tags()->sync($request->tags_id);
***
}
public function edit(Post $post)
{
$tags = Tag::pluck('id', 'title');
***
return view('dashboard.post.edit', compact('post', 'categories', 'tags'));
}
public function update(UpdatePostPut $request, Post $post)
{
//$post->tags()->attach(1);
$post->tags()->sync($request->tags_id);
***
}
}
En cuanto a la vista, queda como:
resources\views\dashboard\post\_form.blade.php
<label for="">Tags</label>
<select class='form-control' multiple name="tags_id[]">
@foreach ($tags as $name => $id)
{{-- <option {{ in_array($id, old('tags_id') ?:
$post->tags->pluck('id')->toArray()) ? 'selected' : '' }} value="{{ $id }}">{{ $name }}
--}}
<option {{ in_array($id, old('tags_id', $post->tags->pluck('id')->toArray())) ?
'selected' : '' }} value="{{ $id }}">{{ $name }}
</option>
@endforeach
</select>
Con el código anterior, creamos un listado de selección múltiple de todas las etiquetas, las etiquetas que se
encuentren asignadas al post, se encuentran seleccionadas por defecto, también mediante la función de old() al
430
igual que hicimos con el resto de los campos, la prioridad es tomada por la selección del usuario y no por lo que
tengamos en el array de etiquetas del post en la base de datos.
También creamos el proceso CRUD para las etiquetas:
<?php
namespace App\Http\Controllers\Dashboard;
use App\Models\Tag;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tag\PutRequest;
use App\Http\Requests\Tag\StoreRequest;
class TagController extends Controller
{
public function index()
{
if (!auth()->user()->hasPermissionTo('editor.tag.index')) {
return abort(403);
}
$tags = Tag::paginate(2);
return view('dashboard/tag/index', compact('tags'));
}
public function create()
{
if (!auth()->user()->hasPermissionTo('editor.tag.create')) {
return abort(403);
}
$tag = new Tag();
return view('dashboard.tag.create', compact('tag'));
}
public function store(StoreRequest $request)
{
if (!auth()->user()->hasPermissionTo('editor.tag.create')) {
return abort(403);
}
Tag::create($request->validated());
return to_route('tag.index')->with('status', 'Tag created');
}
431
public function show(Tag $tag)
{
if (!auth()->user()->hasPermissionTo('editor.tag.index')) {
return abort(403);
}
return view('dashboard/tag/show', ['tag' => $tag]);
}
public function edit(Tag $tag)
{
if (!auth()->user()->hasPermissionTo('editor.tag.update')) {
return abort(403);
}
return view('dashboard.tag.edit', compact('tag'));
}
public function update(PutRequest $request, Tag $tag)
{
if (!auth()->user()->hasPermissionTo('editor.tag.update')) {
return abort(403);
}
$tag->update($request->validated());
return to_route('tag.index')->with('status', 'Tag updated');
}
public function destroy(Tag $tag)
{
if (!auth()->user()->hasPermissionTo('editor.tag.destroy')) {
return abort(403);
}
$tag->delete();
return to_route('tag.index')->with('status', 'Tag delete');
}
}
En el código anterior solamente se muestra el controlador, debes de implementar el resto del código como lo son
las clases Requests, vistas, rutas y permisos asociados, cualquier duda, puedes consultar el código fuente al final
de la sección.
Ahora, veremos algunos otros ejemplos que fueron tomados de la documentación oficial y que puedes tomar de
referencia para conocer cómo emplear el resto de los tipos de relaciones disponibles, si pudiste entender la
relación polimórfica de tipo muchos a muchos que presentamos antes, estos serán muchos más fáciles de
comprender.
432
Caso práctico: Relación uno a muchos
En este ejemplo tenemos como modelo principal el de comentarios, como modelos de tipo "able/seleccionable"
tenemos los de vídeos y posts, es decir, los comentarios pueden ser empleados por la entidad de posts y vídeos:
posts
id - integer
title - string
body - text
videos
id - integer
title - string
url - string
comments
id - integer
body - text
commentable_id - integer
commentable_type - string
En cuanto a los modelos, quedan como:
<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
class Comment extends Model
{
/**
* Get the parent commentable model (post or video).
*/
public function commentable(): MorphTo
{
return $this->morphTo();
}
}
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
class Post extends Model
{
/**
433
* Get all of the post's comments.
*/
public function comments(): MorphMany
{
return $this->morphMany(Comment::class, 'commentable');
}
}
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
class Video extends Model
{
/**
* Get all of the video's comments.
*/
public function comments(): MorphMany
{
return $this->morphMany(Comment::class, 'commentable');
}
}
Caso práctico: Relación uno a uno
En este ejemplo tenemos como modelo principal el de imágenes, como modelos de tipo "able/seleccionable"
tenemos los de usuarios y posts, es decir, estas entidades tienen una imagen asociada
posts
id - integer
name - string
users
id - integer
name - string
images
id - integer
url - string
imageable_id - integer
imageable_type - string
En cuanto a los modelos, quedan como:
<?php
namespace App\Models;
434
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
class Image extends Model
{
/**
* Get the parent imageable model (user or post).
*/
public function imageable(): MorphTo
{
return $this->morphTo();
}
}
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
class Post extends Model
{
/**
* Get the post's image.
*/
public function image(): MorphOne
{
return $this->morphOne(Image::class, 'imageable');
}
}
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;
class User extends Model
{
/**
* Get the user's image.
*/
public function image(): MorphOne
{
return $this->morphOne(Image::class, 'imageable');
}
}
435
Conclusión
En resumen, en los dos últimos ejemplos vemos que tenemos que emplear el método morphTo() para la relación
principal y para los de tipo "able/seleccionable" se emplean los de tipo morphMany() y morphOne()
respectivamente.
Finalmente, los métodos tipo morph() para especificar las relaciones reciben varios parámetros que pueden ser
útiles si Laravel no logra interpretar de manera correcta el nombre de la tabla y relación, por ejemplo:
return $this->morphToMany(Tag::class, 'taggable', 'taggables', 'taggable_id');
Como consideración adicional, como comentamos anteriormente, las relaciones muchos a muchos y uno a
muchos de tipo polimorfismo son las más interesantes en este tipo de relaciones ya que, al querer hacer
'etiquetable/able' una relación de tipo muchos a muchos sin ser polimórfica, tendríamos que duplicar la tabla
pivote para tal fin (y en la de uno a muchos no podríamos crearla para que simule una del tipo polimórfica del
mismo tipo), pero, con el uso de las relaciones polimórficas podemos emplear la misma tabla; en contraparte, el
uso de las relaciones polimórficas para la relación de tipo uno a uno puede ser fácilmente manejados mediante
una relación tradicional del mismo tipo, es decir, que no sean polimórficas, por ejemplo, recordemos que para las
relaciones de tipo uno a uno tenemos:
posts
id - integer
name - string
users
id - integer
name - string
images
id - integer
url - string
imageable_id - integer
imageable_type - string
Pudiéramos tener una relación similar a la anterior en donde los posts y usuarios puedan tener una imagen
mediante:
posts
id - integer
name - string
image_id - integer
users
id - integer
name - string
image_id - integer
436
images
id - integer
url - string
Puedes emplear el instanceOf desde una relación polimórfica para determinar a qué clase pertenece la instancia,
por ejemplo:
class File extends Model
{
***
protected $fillable = ['file', 'type', 'fileable_type', 'fileable_id'];
public function fileable(): MorphTo
{
return $this->morphTo();
}
}
class Book extends Model
{
***
public function files()
{
return $this->morphMany(File::class, 'fileable');
}
}
Al ser files el genérico, puedes verificar el tipo por:
if ($file->fileable instanceof Book) {
// TODO
}
O directamente mediante la consulta de Eloquent:
$file = File::where("fileable_type", Book::class)->where("id", $file->id)->first();
Más información sobre las relaciones en:
https://laravel.com/docs/master/eloquent-relationships
Código fuente del capitulo:
https://github.com/libredesarrollo/book-course-laravel-base-11/releases/tag/v0.11
437
Capítulo 19: Aspectos generales
En este apartado, explicaremos varios temas muy importantes en Laravel y que son muy empleados en el
desarrollo moderno de aplicaciones web con Laravel, pero, debido a su naturaleza, no ha habido oportunidad de
presentarlos hasta ahora y abordar cada uno de ellos en un capítulo, daría como resultado un capítulo
demasiado corto.
En este capítulo, veremos cómo manejar las configuraciones, variables de entorno, crear archivos de ayuda,
enviar correos y temas de este tipo que como comentamos anteriormente, son fundamentales en el desarrollo de
aplicaciones web.
Variables de entorno y configuraciones
Desde el inicio del libro, hemos visto dos esquemas posibles para manejar las configuraciones de Laravel,
mediante las variables de entorno, por ejemplo:
.env
APP_ENV=local
Y mediante los archivos de configuración que incorpora el framework, por ejemplo:
config\app.php
'env' => env('APP_ENV', 'production'),
Y cuando configuramos la caché, el correo u otro siempre mencionamos que puedes modificar uno o el otro, en
este apartado, daremos algunas recomendaciones sobre cuándo debes emplear uno o el otro.
Para esto, debemos de conocer sus ventajas que automáticamente, nos trae sus carencias.
Las variables de entorno son ideales para cuando queremos probar múltiples configuraciones en un proyecto de
una manera rápida, o cuando varias personas están desarrollando sobre un mismo proyecto y tienen distintas
configuraciones del proyecto, en otras palabras, ofrecen un enfoque ágil y rápido para acceder a múltiples
configuraciones pero esto también puede traer inconvenientes, al poder cambiar o eliminar variables de entorno
por error desde un solo archivo puede traer problemas al cambiar de un modo a otro sin darse cuenta o de borrar
alguna variable de entorno necesaria por el proyecto, por lo tanto, emplearla en producción pueden acarrear
problemas precisamente por esta libertad.
Los archivos de configuración son excelentes para tener las configuraciones finales de producción del proyecto,
por lo tanto, es donde deberíamos emplear las variables de entorno en desarrollo y en producción las
configuraciones y esto es algo que ya el framework nos lo dice, ya que, si vemos algunas configuraciones,
veremos muchas veces un esquema como el siguiente:
config\app.php
438
'env' => env('APP_ENV', 'production'),
Lo que significa es que, si no existe la variable de entorno 'APP_ENV', entonces tomará la configuración el valor
de 'production' que es la que tenemos que emplear en producción.
Uno de los grande problemas en el desarrollo de software que consisten en que cuando estamos desarrollando
una aplicación y cada cierto tiempo vamos presentando módulos a producción, es publicar junto con estos
cambios nuestras configuraciones usadas solo en desarrollo y con el esquema presentado anteriormente se
puede solventar con tan solo evitar subir el archivo de .env a producción y manteniendo los valores de
producción en los archivos de configuración correspondientes.
Esto no quiere decir que no es recomendable o profesional emplear variables de entorno a producción, en lo
posible se debería de evitar, pero, puedes emplearla con extremo cuidado, manteniendo un mínimo variables de
entorno en producción que pueden ser imprescindibles para mantenerlo lo más controlado posible.
Otro buen uso de las variables de entorno en producción es cuando hacemos grandes cambios en el proyecto y
lo subimos a producción o hay un error a producción que se complica repararlo mediante la visualización del log y
por lo tanto, queremos probar en producción si todo lo desarrollado funciona como se espera, en estos casos,
puedes descomentar las variables de entorno en producción:
.env
APP_ENV=local
APP_DEBUG=true
Probar el sistema en el servidor y cuando veamos que todo está funcionando como se espera, se vuelven a
comentar:
.env
# APP_ENV=local
# APP_DEBUG=true
Y con esto, se vuelve a evitar tocar las configuraciones del proyecto que son archivos que debemos de manejar
con extremo cuidado.
Crear nuestras propias configuraciones
Es posible crear configuraciones personalizadas para nuestra aplicación, lo cual es extremadamente útil para
poder personalizar estos parámetros cuando sean necesarios, por ejemplo, si quieres instalar alguna billetera
electrónica como PayPal que requiere definir claves de acceso, lo más recomendado es manejar estos accesos
mediante configuraciones para poder editarlas más fácilmente, también son útiles si necesitamos crear cualquier
parámetro adicional para el correcto funcionamiento de la aplicación.
Para ello, debemos de seleccionar el archivo de configuración en donde queremos que esté alojada, aquí lo
recomendado es que escojas el que más se asemeja según la configuración a crear, por ejemplo, si quieres
439
emplear claves para acceder a Dropbox o Google Drive, puedes escoger el database.php o el de app.php si son
credenciales de login social, puedes usar el de auth.php por ejemplo.
Finalmente, creamos la configuración, por ejemplo:
'app_route' => "production",
Al igual que las configuraciones existentes, es posible usar la función de ayuda env() para que tome el valor del
.env si la misma existe allí:
config\app.php
'app_route' => env('APP_ROUTE', "production"),
.env
APP_ROUTE=local
Para acceder a estas configuraciones, usamos la función de ayuda config():
config('app')['env']
En la cual como primer parámetro se especifica el nombre del archivo de configuración y como siguiente
parámetro la clave:
config('app')['env']
También podríamos referenciar directamente la variable de entorno:
env('APP_ROUTE', "production")
Pero si la misma no existe, pueden ocurrir excepciones y errores en el proyecto, así que, lo recomendado es que
crees configuraciones personalizadas en su lugar y las consumas mediante la función de ayuda config().
Crear archivos personalizados
En caso de que sea necesario, podemos crear archivos de configuración personalizados, simplemente debemos
de crear el archivo dentro de la carpeta config del proyecto en el cual, debemos de tener la misma estructura
que mantienen los actuales, por ejemplo:
config\custom.php
<?php
return [
'test_config' => env('TEST_CONFIG', 'VALUE'),
];
440
Y para acceder al archivo personalizado, lo hacemos de igual manera con los archivos de configuración actuales:
config('custom');
Logging
El log desempeñan un papel primordial para guardar registros de posibles problemas que están ocurriendo en la
aplicación para su posterior identificación y resolución de errores, usualmente en local, usamos la herramienta de
debug, junto con la función dd() para poder ver claramente los errores por pantalla, pero, para producción se
emplea algo más elegante como es el uso de los logs que es el tema que cubriremos en este apartado.
Los registros de los logs se pueden generar a partir de errores que ocurren en la aplicación o por métodos
empleados por nosotros mismos como veremos en este apartado, además, podemos personalizar los procesos
de log ya sea para registrar errores en el framework.
En Laravel, los logs se configuran mediante canales como archivos o base de datos.
Lo primero que vamos a hacer es ocasionar algún error en el proyecto, por ejemplo:
app\Http\Controllers\Dashboard\PostController.php
$posts = Post::paginat(10);
Automáticamente si vamos a la ruta un procesa el controlador veremos el error por pantalla:
http://larafirststeps.test/dashboard/post
Call to undefined method App\Models\Post::paginat()
En producción, no deberíamos dejar habilitado este tipo de mensajes para evitar exponer partes críticas del
sistema a usuarios no autorizados, lo que se recomienda es almacenar estos errores en un log del sistema; para
habilitarlo, primero desactivamos el modo debug del proyecto:
.env
APP_DEBUG=false
Y ahora veríamos un error 500 en la ruta de:
http://larafirststeps.test/dashboard/post
Configurar canales para el log
Ahora si vamos a ver el archivo de configuración para el log:
config\logging.php
441
'default' => env('LOG_CHANNEL', 'stack'),
Por defecto emplea la configuración de stack.
Si analizamos el archivo de configuración de los logs, vemos los posibles canales que podemos usar:
config\logging.php
'channels' => [
'stack' => [
'driver' => 'stack',
'channels' => explode(',',env('LOG_STACK', 'single')),
'ignore_exceptions' => false,
],
'single' => [
'driver' => 'single',
'path' => storage_path('logs/laravel.log'),
'level' => env('LOG_LEVEL', 'debug'),
'replace_placeholders' => true,
],
'daily' => [
'driver' => 'daily',
'path' => storage_path('logs/laravel.log'),
'level' => env('LOG_LEVEL', 'debug'),
'days' => env('LOG_DAILY_DAYS', 14),
'replace_placeholders' => true,
],
];
Canales por defecto
Como puedes apreciar en el archivo anterior, existen varios canales para el log que puedes usar para diferentes
propósitos:
● Canal single, registra todos los mensajes en un único archivo de log especificado en la configuración. Es
útil para el desarrollo local o cuando necesita un archivo de registro simple sin rotación de registros.
● Canal daily, registra mensajes en un nuevo archivo de registro cada día, con esto se evita que el archivo
de log crezca demasiado como en el caso anterior, por lo tanto, este es el preferido para cuando tenemos
la aplicación en producción.
● Canal slack, es un controlador Monolog basado en SlackWebhookHandler.
● Canal syslog, envía mensajes de registro al servicio syslog del sistema.
● Canal errorLog, registra mensajes en el registro de errores de PHP, que es específico del sistema.
● Custom channel, logs personalizados que permitirá registrar mensajes en cualquier ubicación.
442
Cada uno de los canales, tienen diversas formas de configuración como el path, para almacenar el archivo:
'path' => storage_path('logs/laravel.log'),
O el nivel que va a escuchar:
'level' => env('LOG_LEVEL', 'debug'),
Del cual hablaremos un poco más adelante.
El driver en donde el más sencillo es el single, para indicar un archivo:
'single' => [
'driver' => 'single',
'path' => storage_path('logs/laravel.log'),
'level' => env('LOG_LEVEL', 'debug'),
'replace_placeholders' => true,
],
Aunque también podemos crear nuestros propios logs de la siguiente manera:
config\logging.php
'custom' => [
'driver' => 'single',
'path' => storage_path('logs/custom.log'),
'level' => 'debug'
]
El tipo de log que nos serviría en la mayoría de los casos, sería el de single (o daily en producción), y cada cierto
tiempo vamos revisando el log para corregir posibles problemas en el sistema.
Para ver el error anterior por el log, configura el driver con:
config\logging.php
'default' => env('LOG_CHANNEL', 'single'),
Al recargar la página con el error, deberias de ver un archivo log generado en:
config\logging.php
Niveles de Log
El nivel de log ofrece un esquema de todos los niveles de log definidos en la especificación RFC 5424. En orden
descendente de gravedad, estos niveles de registro son: emergency, alert, critical, error, warning, notice, info, y
debug.
443
● "emergency": El sistema no se puede utilizar.
● "alert": Se deben tomar medidas de inmediato.
● "critical": Condiciones críticas.
● "error": Errores de tiempo de ejecución que no requieren acción inmediata.
● "warning": Sucesos excepcionales que no son errores.
● "notice": Eventos normales pero significativos.
● "info": Eventos o información interesante.
● "debug": Información de depuración.
Es decir, un nivel de tipo error tiene más peso o importancia que el de warning según la escala presentada
antes, y esto es importante ya que con la opción de level de los logs, podemos especificar hasta qué nivel
queremos registrar.
Desde la aplicación, podemos registrar nuestros propios logs de la siguiente manera en la cual tenemos un
método por cada nivel del log:
Log::emergency($message);
Log::alert($message);
Log::critical($message);
Log::error($message);
Log::warning($message);
Log::notice($message);
Log::info($message);
Log::debug($message);
Formateador para el log
Podemos crear un formato para los logs como el siguiente:
app/Logging/CustomFormatter.php
<?php
namespace App\Logging;
use Monolog\Formatter\LineFormatter;
class CustomFormatter{
public function __invoke($logger)
{
foreach ($logger->getHandlers() as $handle) {
$handle->setFormatter(new LineFormatter("(%datetime%) -
%message%\n"),null,true,true);
}
}
}
444
Los parámetros del método setFormatter() corresponde:
● $format: Define el formato de la línea de registro. Puedes utilizar marcadores de posición como
%datetime%, %channel%, %level_name%, %message%, %context%, %extra%, etc.
● $dateFormat: Define el formato de la fecha y hora en el registro.
● $allowInlineLineBreaks: Si se permite o no saltos de línea en línea.
● $ignoreEmptyContextAndExtra: Si se deben ignorar los contextos y extras vacíos (valor booleano).
● $includeStacktraces: Si se deben incluir trazas de pila en el registro (valor booleano).
Es posible personalizar aspectos como los mostrados en este ejemplo:
<?php
namespace App\Logging;
use Illuminate\Log\Logger;
use Monolog\Formatter\LineFormatter;
class CustomizeFormatter
{
public function __invoke(Logger $logger): void
{
foreach ($logger->getHandlers() as $handler) {
$handler->setFormatter(new LineFormatter(
'[%datetime%] %channel%.%level_name%: %message% %context% %extra%'
));
}
}
}
Puedes obtener más información en:
https://laravel.com/docs/master/logging
Y registramos el formateador en el canal que estemos empleando, en nuestro ejemplo, el de single:
config\logging.php
'single' => [
***
'tap' => [App\Logging\CustomLogging::class],
],
Desde ahora, cada vez que se genere un registro en el log, se hará con el formato anterior.
445
Paginación Personalizada
El sistema de paginación de Laravel es excelente, pero, muchas veces tenemos un pull de datos que los
queremos paginar, es decir, no es posible paginar directamente desde la consulta como hemos hecho antes, por
ejemplo:
Post::paginate(10);
Y para ello, podemos crear nuestros esquemas de paginación personalizados como veremos en este apartado.
Paginator
La clase Paginator no necesita conocer el número total de elementos, es decir, su equivalente viene siendo al
método simplePaginate():
$elements = range(1, 224);
$perPage = 10;
$currentPage = $request->page ?: 1;
$currentElements = array_slice($elements, $perPage * ($currentPage - 1), $perPage);
$res = new
Paginator($currentElements,$perPage,$currentPage,['path'=>'/dashboard/category']);
Al momento de declarar la clase, verás los parámetros que solicita que son justamente los definidos en el
ejemplo anterior, en el caso del path definido, equivale al parámetro de opciones.
LengthAwarePaginator
La clase LengthAwarePaginator acepta los mismos argumentos que Paginator pero requiere un argumento
adicional que corresponde al total de elementos, su equivalente corresponde al método paginate():
$elements = range(1, 224);
$perPage = 10;
$currentPage = $request->page ?: 1;
$currentElements = array_slice($elements, $perPage * ($currentPage - 1), $perPage);
$res = new LengthAwarePaginator($currentElements, count($elements), $perPage, $currentPage,
['path' => '/dashboard/category']);
En ambos casos puedes emplear el método de links() para generar los enlaces paginados:
Illuminate\View\View {#315 ▼ // routes\web.php:21
#factory:
Illuminate\View
\
Factory {#312 …25}
446
#engine:
Illuminate\View\Engines
\
CompilerEngine {#319 ▶}
#view: "pagination::simple-tailwind"
#data: array:1 [▶]
#path:
"***\vendor\laravel\framework\src\Illuminate\Pagination/resources/views/simple-tailwind.bla
de.php"
}
Enviar correos electrónicos
Podemos configurar el envío de correos muy fácilmente en Laravel, si ya tienes un servicio que provee tu hosting
o similar, lo único que debes de hacer es crear una dirección de correo con su contraseña.
A partir de aquí, debes de configurar algunos parámetros, si no los conoces porque el servidor de correos que
estás empleando es de un servicio, debes de preguntar a tus proveedores de servicio; en el caso de Hostinger
serían los siguientes:
config/mail.php
'smtp' => [
'transport' => 'smtp',
'host' => env('MAIL_HOST', 'smtp.hostinger.com'),
'port' => env('MAIL_PORT', 465),
'encryption' => env('MAIL_ENCRYPTION', 'ssl'),
'username' => env('MAIL_USERNAME','<EMAIL>'),
'password' => env('MAIL_PASSWORD',"<PASSWORD>"),
'timeout' => null,
'auth_mode' => null,
],
O puedes emplear un servicio de pruebas en caso de que no tengas acceso a un servidor de correos real como
mailtrap:
https://mailtrap.io
En el cual, debes de ir a la página anterior, crearte una cuenta que es completamente gratuita, crear un inbox y
configurar en tu proyecto.
.env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=ec5cbede982042
447
MAIL_PASSWORD=********0be7
Clase Mailable
Para enviar cualquier correo, debemos de emplear una clase, al igual que ocurre cuando definimos un modelo,
controlador o request, debemos de crear una clase con una estructura específica:
app\Mail\OrderShipped.php
<?php
namespace App\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
class OrderShipped extends Mailable
{
public $email;
public $title;
public $content;
use Queueable, SerializesModels;
public function __construct($email, $title, $content)
{
$this->email = $email;
$this->title = $title;
$this->content = $content;
}
/**
* Get the message envelope.
*/
public function envelope(): Envelope
{
return new Envelope(
subject: 'Order Shipped',
);
}
448
/**
* Get the message content definition.
*/
public function content(): Content
{
return new Content(
view: 'emails.subscribe',
);
}
/**
* Get the attachments for the message.
*
* @return array<int, \Illuminate\Mail\Mailables\Attachment>
*/
public function attachments(): array
{
return [];
}
}
Para ello, usamos el comando de:
$ php artisan make:mail OrderShipped
El método de envelope() lo empleamos para definir el asunto, el de content() para el cuerpo del mensaje, allí
especificamos la vista y el de attachments() para archivos adjuntos, el constructor es básico en las clases de
PHP y se emplea para inicializar propiedades u otras tareas al momento de crear el objeto o instancia de la clase.
También definimos algunas propiedades de ejemplo como lo son el título, contenido e email, puedes crear otros o
modificar los definidos según tus necesidades.
También podemos emplear un solo método para definir el asunto y contenido:
app\Mail\SubscribeEmail.php
<?php
namespace App\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
449
class SubscribeEmail extends Mailable
{
use Queueable, SerializesModels;
public $email;
public $title;
public $content;
public function __construct($email, $title, $content)
{
$this->email = $email;
$this->title = $title;
$this->content = $content;
}
public function build()
{
return $this->subject($this->title)->view('subscribe');
}
}
Desde la misma, podemos personalizar los argumentos a recibir, como email asunto o contenido del correo y
retornar una vista de blade que corresponde al cuerpo del correo; por ejemplo:
Creamos la vista que puede tener cualquier formato:
resources\views\emails\subscribe.blade.php
<p>Hi<br>
{!! $content !!}
Enviar correos de forma individual
Para enviar los correos, creamos una instancia de la siguiente manera:
Mail::to('no-reply@example.net.com')->send(new SubscribeEmail('contact@gmail.com', $title,
$content));
Parámetros
No está limitado a especificar simplemente los destinatarios "to" al enviar un mensaje. Eres libre de configurar
destinatarios "to", "cc" y "bcc" empleando sus respectivos métodos:
Mail::to($request->user())
->cc($moreUsers)
->bcc($evenMoreUsers)
->send(new SubscribeEmail(contact@gmail.com', $title, $content));
450
CC y BCC/CCO
Un CC es una forma de enviar copias adicionales de un correo electrónico a otras personas "copia de carbono",
mientras que BCC es lo mismo que el CC pero esta lista de destinatarios o personas permanecen ocultas, es
decir, no aparecen en la firma del correo electrónico.
Enviar correos en masa
Una de las formas en las cuales podemos enviar múltiples correos en masa o lote, es la de enviar en un mismo
contacto a múltiples correos, usualmente este es un caso delicado ya que, si hacemos algo como lo siguiente:
Mail::to('no-reply@example.net.com')
->cc(['hideemail1@gmail.com','hideemail2@gmail.com','hideemail3@gmail.com'])->send(new
SubscribeEmail(contact@gmail.com', $title, $content));
El correo va a exponer todos los emails de los usuarios, situación que usualmente es un problema por la
exposición de todos los correos a todos los destinatarios; en vez de emplear la opción de cc podemos emplear la
opción de bcc la cual permite ocultar los destinatarios:
Mail::to('no-reply@example.net.com')
->bcc(['hideemail1@gmail.com','hideemail2@gmail.com','hideemail3@gmail.com'])->send(new
SubscribeEmail(contact@gmail.com', $title, $content));
Ahora veremos que los emails definidos en BBC aparecen ocultos:
Figura 19-1: Cabecera de emails
Y no presentes como en la imagen anterior.
Helpers
En Laravel, un helper es una función de alcance global lo que significa que, desde cualquier clase de
componente, controlador, modelo, vista… podemos acceder a la misma para desencadenar su funcionalidad
correspondiente. Los helpers no son algo inventado por Laravel, existen en multitud de otros frameworks que
emplean estos esquemas de colocar en un solo lugar funciones que no forman parte domo tal para la lógica de
negocios, sin no, son de aspectos más generales y que se emplean en varias partes de la aplicación.
Los helpers han ganado cada vez más cabida en las últimas versiones de Laravel, creando funciones de ayuda
para otros procesos que anteriormente eran Facade (que se consideran antipatrones), por lo tanto, podemos usar
los del sistema o crear nosotros los propios como mostraremos en este capítulo.
Las funciones de ayuda o helpers son funcionalidades que podemos crear a lo largo de la app que son de
propósito general; generalmente NO están vinculadas a un proceso o lógica de negocio, si no son funciones de
propósito más general como la de hacer algún proceso con textos, números, imágenes... y en Laravel por
451
supuesto podemos crear este tipo de procesos o funciones de ayuda: para eso, tenemos que cumplir 3 sencillos
pasos:
Crear el archivo con las funciones
Tenemos que crear en alguna parte de nuestro proyecto la función de ayuda, generalmente lo hacemos dentro de
app, en algún directorio, por ejemplo, una carpeta llamada Helpers:
app\Helpers\helper.php
<?php
public function hello(string $hello){
return "Hello $hello";
}
Allí, definimos todas nuestras funciones, inclusive podemos crear más archivos con funciones de ayuda en caso
de que quieras tener algún orden en tus funciones; es importante mencionar que puedes crear tantos archivos de
ayuda en esta carpeta (u otras carpetas o subcarpetas) para tu aplicación, por lo tanto, no estás limitado a un
único archivo.
Registrar en el composer.json
Una vez creada tus archivos o archivo de ayuda con tus funciones, lo siguiente que tenemos que hacer, sería
registrarlo para poder emplearlo de manera global en el proyecto; para esto, tenemos que registrarlo en una regla
llamada autoload, en la key de files, coloca la referencia a tus archivos helpers:
"autoload": {
"psr-4": {
"App\\": "app/",
"Database\\Factories\\": "database/factories/",
"Database\\Seeders\\": "database/seeders/"
},
"files" : ["app/Helpers/helper.php"]
},
Por cada archivo de ayuda que tengas, lo registras en el array de files.
Refrescar dependencias
Con todo correctamente registrado, lo siguiente que tenemos que hacer es ejecutar un comando de composer
para que al igual que ocurre con cualquier otro paquete de Laravel, lo indexe en nuestro archivo de arranque de
composer:
$ composer dump-autoload
Ya con esto podemos emplear nuestras funciones en cualquier parte de nuestro proyecto, ya sea una vista,
controlador u otro:
452
hello('My custom MSJ')
Colecciones
Las colecciones no son nada nuevo, hasta este momento hemos empleado las colecciones en los desarrollos
anteriores, cada vez que teníamos una lista como respuesta de la base de datos para crear una tabla en una
vista, se emplean colecciones:
Category::all();
Y esto es algo muy fácil de ver si imprimir el resultado anterior en modo debug o tinker:
dd(Category::all());
Illuminate\Database\Eloquent\Collection
#items: array:N
}
Las colecciones en Laravel son una herramienta esencial en Laravel para manejar listados, pero, también
podemos crear nuestras propias colecciones como veremos en este apartado.
Las colecciones no son más que los arrays de PHP pero con vitaminas, esto quiere decir a que son los
mismos arrays pero con un conjunto de funcionalidades adicionales para que sean más fáciles y amigables su
uso bajo la clase:
Illuminate\Support\Collection
Aunque, esta clase no solamente es empleada de manera interna, también podemos emplearlo de manera
manual, es decir, podemos crear una colección de un array en PHP:
$personas = [
["nombre" => "usuario 1", "edad" => 50],
["nombre" => "usuario 2", "edad" => 70],
["nombre" => "usuario 3", "edad" => 10],
];
Para ello, tenemos varias formas:
use Illuminate\Support\Facades\Collection;
***
$collection1 = collect($personas);
//dd($collection1);
$collection2 = new Collection($personas);
//dd($collection2);
$collection3 = Collection::make($personas);
//dd($collection3);
453
Con la colección, ahora es posible emplear métodos propios de la colección de todo tipo como el de filtro:
$collection2->filter(function($value,$key){
return $value['edad'] > 17;
})
El cual, devuelve una colección de aquellos elementos que cumplan la condición, en este ejemplo en donde la
edad sea mayor a 17:
Illuminate\Support\Collection {#5178
all: [
[
"nombre" => "usuario 1",
"edad" => 50,
],
[
"nombre" => "usuario 2",
"edad" => 70,
],
],
}
Al devolver el método de filter() una colección, podemos encolar otras operaciones sobre colecciones como la de
sumar:
$collection2->filter(function($value,$key){
return $value['edad'] > 17;
})->sum('edad'));
Que en este caso, no devuelve una colección, sino un entero:
120
Otro método interesante es el de intercepción que elimina cualquier valor de la colección original que no esté
presente en el array suministrado:
$collection = collect(['Desk', 'Sofa', 'Chair']);
$intersect = $collection->intersect(['Desk']);
Y devuelve:
$collection->intersect(['Desk'])
= Illuminate\Support\Collection {#5118
all: [
"Desk",
454
],
}
Estos métodos son propios de las colecciones, puedes revisar la documentación oficial para conocer la enorme
cantidad de métodos que solamente tenemos disponibles al emplear las colecciones y que no existen con los
arrays en:
https://laravel.com/docs/master/collections
Operaciones transaccionales en la base de datos
Dependiendo del tipo de aplicación que queramos construir, muchas veces es necesario realizar múltiples
operaciones en la base de datos de manera segura, por ejemplo, una compra de un artículo de un libro que
debemos de hacer pasos como los siguientes:
1. Registrar el identificador de pago en la base de datos.
2. Descontar las cantidades del inventario.
3. Registrar el producto en algún listado de compras del cliente.
Esto por comentan unos posibles pasos, pero, pueden ser más y pueden estar en cualquier orden, hay muchos
otros casos en que es necesario hacer varias operaciones en la base de datos y si una de estas falla, entonces
todas las operaciones anteriores deberían de ser revertidas para evitar dejar el proceso en un estado intermedio;
en el ejemplo anterior, pudiéramos decir que si llego al paso dos y por alguna razón ya no hay el producto en
inventario, la operación anterior daría un error y no pudiera registrar el paso tres al no poder descontar el
producto del inventario, dejando la orden en un limbo ya que el paso uno si se hubiera completado.
Y es aquí donde entra el uso de las operaciones transaccionales que permiten agrupar un conjunto de consultas
en una única unidad de trabajo, asegurando que todas se ejecuten correctamente o ninguna de ellas lo haga. En
otras palabras, si una consulta falla, se revierten todas las modificaciones realizadas previamente en la base de
datos. En nuestro ejemplo anterior, si hay un problema en cualquier de los pasos, Laravel revierte todas las
operaciones, con esto, garantizamos la integridad de los datos, evitando que la orden quede en un limbo y
atomicidad de las operaciones, ya que todas las operaciones se realizan en una misma unidad de trabajo y si
alguna de ellas falla todas las operaciones son revertidas y con esto cumplimos el objetivo principal de que la
aplicación sea segura ante posibles problemas como los mencionamos antes.
Aquí tenemos un ejemplo de su uso:
use Illuminate\Support\Facades\DB;
try {
// inicia la transaccion
DB::beginTransaction();
// Realiza tus consultas aquí
DB::table('users')->insert(['name' => 'John Doe']);
DB::table('orders')->insert(['user_id' => 1, 'total_amount' => 100]);
DB::commit(); // Confirma los cambios en la base de datos
455
} catch (\Exception $e) {
DB::rollback(); // Revierte los cambios en caso de error
return $e->getMessage();
}
En este ejemplo:
● DB::beginTransaction() inicializa la transacción.
● Dentro del bloque try, realizamos nuestras todas las operaciones a la base de datos mediante nuestros
modelos o similares.
● Si todas las operaciones que queríamos realizar fueron resueltas exitosamente, llamamos al método
DB::commit() para confirmar los cambios.
● Si ocurre un error, el bloque catch llama a DB::rollback() para revertir todos los cambios que se hayan
podido realizar.
También se puede implementar mediante un callback:
DB::transaction(function () {
// Realiza tus consultas aquí
DB::table('users')->insert(['name' => 'John Doe']);
DB::table('orders')->insert(['user_id' => 1, 'total_amount' => 100]);
DB::commit();
});
Eager loading y lazy loading
El eager loading y lazy loading son dos técnicas que tenemos disponibles para recuperar datos relacionados al
trabajar con modelos Eloquent. Y hay que conocerlas al detalle para emplear técnicas que más se ajuste a
nuestras necesidades; no hay una técnica mejor que la otra, ambas se emplean para optimizar el rendimiento de
la aplicación al reducir la cantidad de consultas a la base de datos necesarias para obtener datos relacionados.
Vamos a conocerlas en detalle.
Lazy Loading (Carga perezosa)
También conocido como “carga bajo demanda” o “carga perezosa”; este es el comportamiento predeterminado en
Eloquent por defecto que se emplea al emplear las relaciones foráneas.
El funcionamiento de esta técnica consiste en que al momento de obtener una colección de datos relacionados
(entiéndase un listado de registros provenientes de una relación, por ejemplo, el listado de publicaciones dado la
categoría) Eloquent solo recupera los datos de la base de datos en el momento en que los solicitas. Es
decir, que, para cada acceso a un registro relacionado, se ejecuta una consulta separada a la base de datos.
Esto usualmente trae consigo el famoso problema de tipo N+1, en donde se ejecutan N+1 consultas a la base de
datos en una misma tarea.
Ya en nuestro módulo de dashboard tenemos este problema, por una parte, tenemos la consulta principal:
app\Http\Controllers\Dashboard\PostController.php
456
public function index()
{
if(!auth()->user()->hasPermissionTo('editor.post.index')){
return abort(403);
}
$posts = Post::paginate(10);
return view('dashboard/post/index', compact('posts'));
}
Y desde la vista, referenciamos la categoría, por defecto, Laravel emplea la técnica de lazy loading para obtener
los datos relacionados, por lo tanto, cada vez que se realice una consulta, se va a realizar una consulta adicional,
desde el listado, estamos obteniendo la categoría y con esto una consulta adicional por cada post en la página:
resources\views\dashboard\post\index.blade.php
@foreach ($posts as $p)
****
<td>
{{ $p->category->title }}
***
Lo cual, es el problema del N+1, en donde N en nuestro ejemplo es el tamaño de la página, unos 10 que
representan las categorías obtenidas desde el post y el 1 es la consulta principal para obtener los datos
paginados.
Por suerte, Laravel en versiones modernas permite detectar este problema muy fácilmente mediante la siguiente
configuración:
app\Providers\AppServiceProvider.php
<?php
namespace App\Providers;
use Illuminate\Database\Eloquent\Model;
***
class AppServiceProvider extends ServiceProvider
{
public function boot(): void
{
Model::preventLazyLoading(app()->isProduction());
}
}
457
Con el AppServiceProvider podemos cargar clases esenciales de nuestro proyecto para integrarlos en la
aplicación.
Así que, si ahora intentamos acceder a la página anterior, veremos un error por pantalla como el siguiente:
Attempted to lazy load [category] on model [App\Models\Post] but lazy loading is disabled.
El sistema de detección del problema N+1 en Laravel no es perfecto, ya que, si solamente tuviéramos una
paginación de 1 nivel, no ocurriría la excepción anterior.
Con truco adicional, podemos ver las consultas realizadas para resolver una petición del cliente:
routes/web.php
DB::listen(function ($query){
echo $query->sql;
// Log::info($query->sql, ['bindings' => $query->bindings, 'time' => $query->time]);
});
También podemos emplear la extensión de debugbar, pero esto lo veremos en el siguiente capítulo, si habilitas el
script anterior, verás que ocurren más de 15 consultas, una de ellas para la sesión del usuario autenticado,
permisos y roles la de los post y 10 para las categorías si tienes una paginación de 10 niveles, Esto es estupendo
para detectar el problema pero con el inconveniente de que nuestra página de detalle para la categoría ya no
funciona, para corregirla, vamos a introducir el siguiente tema.
Vamos a crear otro ejemplo, vamos a emplear la relación de posts que tenemos en la categoría:
app\Models\Category.php
class Category extends Model
{
***
function posts() {
return $this->hasMany(Post::class);
}
}
Si desde la vista obtenemos la relación:
resources\views\dashboard\category\index.blade.php
@foreach ($categories as $c)
***
<td>
{{ $c->posts }}
Veremos la excepción anterior, así que, obtenemos los posts junto con las categorías:
458
app\Http\Controllers\Dashboard\CategoryController.php
$categories = Category::with('posts')->paginate(10);
El problema de este esquema es que va a traer todos los posts asociados a una categoría, y un post es una
relación algo pesada, ya que contiene la columna de content con todo el contenido HTML, y si esto sumamos las
10 categorías en el listado el problema se multiplica.
Existen varias formas en las cuales podemos especificar las columnas que queremos obtener de la relación
secundaria:
$posts = Post::with('category:id,title')->paginate(10);
$posts = Post::with(['category' => function($query){
// $query->where('id',1);
$query->select('id','title');
}])->paginate(10);
Aunque estos esquemas no son soportados por la relación de posts de las categorías:
$categories = Category::with('posts:id,title')->paginate(10);
$categories = Category::with(['posts' => function($query){
// $query->where('id',1);
$query->select('id','title');
}])->paginate(10);
De momento, no podemos solucionar el problema y con esto la excepción ya que para ello necesitamos o
cambiar la petición para que emplee los JOINs, o presentar el siguiente tema que es el de Eager Loading que
veremos a continuación.
Eager Loading (Carga ansiosa)
Con este proceso podemos realizar todas las operaciones en una sola consulta, si nos vamos al ejemplo anterior,
que tenemos N+1 consultas a la base de datos, solamente realizaremos una sola consulta y con esto, mejorar el
rendimiento de la aplicación, para ello, debemos de especificar la relación al momento de realizar la consulta
principal:
app\Http\Controllers\Dashboard\PostController.php
$posts = Post::with(['category'])->paginate(10);
Si vamos a nuestra página de detalle de categorías, veremos que funciona correctamente.
Esta función tiene muchas implementaciones, por ejemplo, si tenemos una relación anidada:
class Tutorial extends Model
{
459
***
}
También podemos definir en el modelo el uso de esta técnica por defecto:
class Post extends Model
{
protected $with = ['category'];
}
El método de with() lo podemos extender en relaciones más complejas, como la siguiente que tenemos una
relación de dos niveles:
class Tutorial extends Model
{
***
public function sections()
{
return $this->hasMany(Tutorial::class);
}
}
class TutorialSection extends Model
{
***
public function tutorial()
{
return $this->belongsTo(Tutorial::class);
}
public function classes()
{
return $this->hasMany(Tutorial::class);
}
}
class TutorialSectionClass extends Model
{
***
public function tutorialSection()
{
return $this->belongsTo(TutorialSection::class);
}
}
Podemos hacer consultas de la siguiente forma, indicando más de una relación a obtener:
$posts = Post::with(['categories','tags'])->get();
460
O si quieres colocar alguna condición sobre algunas de las relaciones, puedes implementar un callback de la
siguiente forma:
Tutorial::with('sections')->with(['sections.classes' => function ($query) {
$query->where('posted', 'yes');
$query->orderBy('orden');
}])->where('posted', 'yes')->find($tutorial->id);
}
Conclusión
Es importante mencionar que no hay una técnica mejor que la otra, ya que, todo depende de lo que quieras
realizar, pero, lo podemos simplificar de la siguiente manera, si tenemos una colección de registros relacionados
mediante una FK como en el ejemplo anterior y no vas a emplear la relación foránea, la técnica que deberías de
emplear sería la de carga perezosa, pero, si vas a emplear la colección con los registros relacionados, debes de
emplear la carga ansiosa.
Finalmente, por cada relación especificada en el with() solamente suma una consulta adicional, también
recuerda especificar la columnas en la medida de lo posible al momento de obtener las relaciones.
Mutadores y accesores
Muchas veces queremos personalizar la respuesta que obtenemos en Eloquent, poder establecer un formato de
fecha u otros formatos sin necesidad de hacerlo de manera manual en el controlador o similar; también, muchas
veces queremos es hacer justo lo contrario, en vez de modificar o mutar los datos en el GET o al momento de
obtener los datos, queremos personalizar los datos al momento de hacer el SET, por ejemplo, para dar valores
por defecto; en estos casos empleamos los los mutadores y accesores respectivamente, que son dos
mecanismos que permiten personalizar cómo interactúas con los datos en tus modelos Eloquent.
Mutadores
Los mutadores permiten modificar en un método en el modelo los valores de los atributos antes de guardarlos en
la base de datos, para ello, debemos de crear una función con el nombre del atributo:
use Illuminate\Database\Eloquent\Casts\Attribute;
***
class Post extends Model
{
***
protected function title(): Attribute
{
return Attribute::make(
get: fn (string $value) => ucfirst($value),
);
}
}
461
Si el atributo tiene un nombre compuesto separado por un _, se emplea la siguiente sintaxis:
use Illuminate\Database\Eloquent\Casts\Attribute;
***
class Post extends Model
{
***
protected function categoryId(): Attribute
{
***
}
}
Otra forma de crear mutadores, es definiendo un método set con el siguiente patrón:
set<ATTRIBUTE>Attribute
Por ejemplo:
public function setTitleAttribute($value): void {
$this->attributes['title'] = ucfirst($value);
}
Accesores
Los accesores permiten dar un formato al momento de acceder a los valores de los atributos, para ello:
use Illuminate\Database\Eloquent\Casts\Attribute;
***
class Post extends Model
{
***
protected function title(): Attribute
{
return Attribute::make(
get: fn (string $value) => ucfirst($value),
set: fn (string $value) => strtolower($value),
);
}
}
También podemos emplear un patrón similar al anterior para el nombrado del método:
public function getTitleAttribute($value): string {
return strtolower($value);
}
462
También es posible crear otros atributos, por ejemplo, para especificar el nombre completo de un usuario cuyo
atributo no está definido y es una combinación entre el nombre y el apellido:
public function fullName(): Attribute {
return new Attribute(get: fn() => $this->first_name . ' ' . $this->last_name);
}
Localization y traducciones
Con las funciones que ofrece Laravel de manera nativa para el manejo de la localización, es decir, el lugar donde
está siendo consumida la aplicación y con esto, poder ofrecer otro servicio, como lo es el de la traducción de
textos de manera automática en base a la localización o por selección del usuario. En este apartado, veremos
implementar ambos temas.
Cadenas de textos para la traducción
Laravel proporciona dos formas de gestionar cadenas de traducción que son las empleadas para mostrar los
textos traducidos de nuestra aplicación en diferentes idiomas; en ambos casos, debemos de crear una carpeta
para almacenar los mismos:
/lang
En donde creamos nuestros archivos de traducción ya sean PHP:
/lang
/en
messages.php
/es
messages.php
O en JSONs:
/lang
en.json
es.json
En el libro, usaremos el formato de los archivos en PHP; puedes crear tantos como quieras y modularizar los
mensajes según nuestras preferencias.
Finalmente, somos nosotros los que debemos de definir las cadenas traducidas y personalizar las mismas
cambiando el texto predefinido por la aplicación y creando las propias; mediante la pareja de clave/valor, de
indica la clave y la traducción; por ejemplo:
return [
'welcome' => 'Welcome to our application!',
];
463
Publicar los archivos de idioma
De forma predeterminada, al momento de crear un proyecto en Laravel no incluye la carpeta de lang; para
generarla, tenemos el siguiente comando de artisan:
$ php artisan lang:publish
El comando lang:publish creará el directorio lang en su aplicación y publicará el conjunto predeterminado de
archivos de idioma utilizados por Laravel:
Figura 19-1: Carpeta para las traducciones
Crear las cadenas de traducción
Las cadenas de texto para las traducciones se almacenan en archivos en la carpeta de lang, en estos archivos,
se almacenan textos que son internos a Laravel y provistos por nosotros:
/lang
/en
messages.php
/es
messages.php
Creemos los siguientes:
lang/en/messages.php
<?php
return [
'welcome' => 'Welcome to our application!',
];
lang/es/messages.php
<?php
return [
464
'welcome' => 'Bienvenido a nuestra aplicación!',
];
Y los empleamos a lo largo de la aplicación:
echo __('messages.welcome')
Como puedes apreciar, debemos de colocar el nombre del archivo y la clave como clave para la función de __()
la cual devuelve el texto traducido; este esquema, lo podemos emplear tanto en el controlador y similar como en
la vista.
Crearemos algunas traducciones de ejemplo para nuestra aplicación, en este caso nos interesa traducir al
español, aunque puedes seleccionar otro idioma si quieres:
lang\es\dashboard.php
<?php
return [
'dashboard' => 'Dashboard',
'title' => 'Título',
'posted' => 'Posted',
'category' => 'Categoría',
'options' => 'Opciones',
'tag' => 'Etiqueta',
'role' => 'Role',
'permission' => 'Permiso',
'user' => 'Usuario',
];
lang\en\dashboard.php
<?php
return [
'dashboard' => 'Dashboard',
'title' => 'Title',
'posted' => 'Posted',
'category' => 'Category',
'options' => 'Options',
'tag' => 'Tag',
'role' => 'Role',
'permission' => 'Permission',
'user' => 'User',
];
465
En los ejemplos anteriores, son solamente unos ejemplos, ya que, deberias de colocar TODOS los textos que
quieras traducir por módulo, en el ejemplo anterior, son textos que aparecen en el módulo de dashboard, pero,
también hay más textos en el módulo web o blog los cuales puedes colocar en otro archivo de traducción.
Y ahora, se emplean los textos traducidos en de ejemplo a nivel de las vistas; por ejemplo:
resources\views\layouts\navigation.blade.php
<div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
<x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
{{ __('dashboard.dashboard') }}
</x-nav-link>
@can('editor.post.index')
<x-nav-link :href="route('post.index')" :active="request()->routeIs('post.index')">
{{ __('Post') }}
</x-nav-link>
@endcan
@can('editor.category.index')
<x-nav-link :href="route('category.index')"
:active="request()->routeIs('category.index')">
{{ __('dashboard.category') }}
</x-nav-link>
@endcan
@can('editor.tag.index')
<x-nav-link :href="route('tag.index')" :active="request()->routeIs('tag.index')">
{{ __('dashboard.tag') }}
</x-nav-link>
@endcan
@if (auth()->user()->hasRole('Admin'))
<x-nav-link :href="route('role.index')" :active="request()->routeIs('role.index')">
{{ __('dashboard.role') }}
</x-nav-link>
<x-nav-link :href="route('permission.index')"
:active="request()->routeIs('permission.index')">
{{ __('dashboard.permission') }}
</x-nav-link>
@endif
@can('editor.user.index')
<x-nav-link :href="route('user.index')" :active="request()->routeIs('user.index')">
{{ __('dashboard.user') }}
</x-nav-link>
@endcan
</div>
Y para el listado:
466
resources\views\dashboard\post\index.blade.php
<tr>
<th>
Id
</th>
<th>
{{ __('dashboard.title') }}
</th>
<th>
{{ __('dashboard.posted') }}
</th>
<th>
{{ __('dashboard.category') }}
</th>
<th>
{{ __('dashboard.options') }}
</th>
</tr>
Traducir mensajes internos al framework
También, recuerda traducir mensajes internos al framework, ideales cuando mostramos errores o excepciones,
es decir, a nivel del framework; para ello, puedes realizar una búsqueda en Internet, por ejemplo:
traducción laravel github
Y con esto, es posible que encuentres varios repositorios de ejemplo, claro ésta, que tienes que colocar el idioma
que quieres traducir, por ejemplo:
https://github.com/Laraveles/spanish/tree/master/resources/lang/es
https://github.com/amendozaaguiar/laravel-lat-es/tree/main/resources/lang/es
Y copias los archivos del framework traducidos:
● message.php
● pagination.php
● validation.php
● password.php
Como recomendación, revisa los textos de manera visual y ver si todo corresponde con los archivos para tu
proyecto.
Configurar la configuración regional (localización)
El idioma predeterminado para la aplicación se almacena en la opción de:
config/app.php
467
'locale' => env('APP_LOCALE', 'en'),
También, es posible configurar un "idioma alternativo", que se utilizará cuando el idioma predeterminado no
contenga una cadena de traducción determinada; se puede configurar en el archivo de configuración
config/app.php y su valor generalmente se establece mediante la variable de entorno
APP_FALLBACK_LOCALE.
Vamos a crear un método para personalizar el idioma de la aplicación mediante:
routes\web.php
use Illuminate\Support\Facades\App as AppLaravel;
Route::get('/set_locale/{locale}', function (string $locale) {
if (! in_array($locale, ['en', 'es'])) {
abort(400);
}
AppLaravel::setLocale($locale);
// ...
});
Como puedes apreciar, mediante el Facade de App::setLocal() es posible establecer el idioma de la aplicación y
con esto, Laravel establece las cadenas de textos correspondientes para la traducción, es importante mencionar,
que la operación NO es persistente y debemos hacerlo cada vez que el usuario realiza una petición.
También, podemos obtener el idioma establecido:
use Illuminate\Support\Facades\App;
$locale = App::currentLocale();
if (App::isLocale('en')) {
// ...
}
También puedes acceder al locale mediante una función de ayuda:
app()->setLocale($language);
Middleware para prefijo de lenguaje en la URL
El siguiente desarrollo que vamos a realizar, es mediante un middleware detectar el idioma configurado por el
usuario y establecerlo mediante las cadenas de textos de traducciones que definimos antes, además, también
definimeros las siglas o etiqueta del lenguaje en la URL para devolver la traducción acorde; para ello:
468
$ php artisan make:middleware LanguagePrefixMiddleware
Que tendrá el siguiente contenido:
app\Http\Middleware\LanguagePrefixMiddleware.php
<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
class LanguagePrefixMiddleware
{
/**
* Handle an incoming request.
*
* @param \Closure(\Illuminate\Http\Request):
(\Symfony\Component\HttpFoundation\Response) $next
*/
public function handle(Request $request, Closure $next): Response
{
$language = $request->segment(1);
if(!in_array($language,['es','en'])){
return redirect('/es/blog');
}
app()->setLocale($language);
return $next($request);
}
}
Con el código anterior, solamente se redirecciona cuando las etiquetas de idioma no están disponibles en la URL,
luego, se establece el lenguaje correspondiente.
Y usamos el middleware en las rutas:
Route::get('/{lang}/mi-ruta', 'MiControlador@miMetodo');
Por ejemplo, en nuestra aplicación:
function routeBlog() {
469
Route::get('', [BlogController::class, 'index'])->name('blog.index');
Route::get('detail/{id}', [BlogController::class, 'show'])->name('blog.show');
}
Route::group(['prefix' => '{locale}/blog','middleware' => LanguagePrefixMiddleware::class],
function () {
routeBlog();
});
Route::group(['prefix' => 'blog','middleware' => LanguagePrefixMiddleware::class], function
() {
function () {
routeBlog();
});
Como puedes apreciar, creamos una función routeBlog() para agrupar las rutas a la cuales queremos verificar el
uso del lenguaje, en este ejemplo, las del blog, para que se pueda consumir mediante el locale:
es/blog/*
en/blog/*
Y sin el locale, y en este caso, se redirecciona al idioma español según la redirección definida en el middleware:
blog/*
Atributos
Podemos pasar parámetros en las traducciones de la siguiente manera:
return [
'welcome' => 'Welcome :name',
];
Mayúsculas
Siguiendo con el uso de los parámetros, también podemos indicar mayúsculas para el parámetro indicando la
primera letra del parámetro en mayúscula:
return [
'welcome' => 'Welcome :Name,
];
O todo el parámetro:
return [
'welcome' => 'Welcome :NAME,
];
470
Y le pasamos el parámetro:
{{ __('welcome', ['name' => 'andres']) }}
También, podemos emplear las funciones de Laravel/PHP:
{{__('dashboard.welcome',['name' => ucfirst('andres')])}}
Más información en la documentación oficial:
https://laravel.com/docs/master/localization
Atributos personalizados en @vite
Ya conocemos que cargamos nuestros archivos CSS y JS mediante:
@vite(['resources/css/blog.css'])
@vite(['resources/js/blog.js'])
En nuestros archivos blade; al momento del renderizado, obtenemos las etiquetas de HTML como:
<link rel="preload" as="style"
href="https://www.desarrollolibre.net/build/assets/blog-6Y1hNTHC.css" />
<script type="module"
src="https://www.desarrollolibre.net/build/assets/blog-Bdalpt8p.js"></script>
Pero, qué pasa si quieres personalizar a nivel de atributos, por ejemplo, el async para los JavaScript; para ello,
podemos agregar las modificaciones a nuestro provider:
app/Providers/AppServiceProvider.php
use Illuminate\Support\Facades\Vite;
***
class AppServiceProvider extends ServiceProvider
{
***
public function boot(): void
{
Vite::useScriptTagAttributes([
'data-turbo-track' => 'reload',
'async' => true,
'integrity' => false,
]);
Vite::useStyleTagAttributes([
'data-turbo-track' => 'reload',
]);
471
}
}
Remover la carpeta public o index.php de la URL en Laravel
Muchas veces cuando estamos desarrollando un proyecto en Laravel y lo pasamos a producción, vemos errores
que aparece en la URL, la carpeta public o el archivo index.php:
// Valid URL
https://example.com/blog
// Invalid URL
https://example.com/index.php/blog
https://example.com/public/blog
https://example.com/public/blog
Lo cual, si tenemos un blog, esto nos puede traer penalizaciones con el SEO; en esta entrada vamos a ver como
reparar este problema, no desde el htaccess que muchas veces no funciona y que hay muchos ejemplos de
cómo hacerlo en Internet, si no, mediante código PHP, por lo tanto, si estás desesperado, esta es la última
medida que puedes implementar.
Para remover la carpeta public y/o index.php de la URL, lo que debemos de hacer es ir al proveedor de nuestra
aplicación:
app/Providers/AppServiceProvider.php
Que es una especie de middleware, y creamos una función como la siguiente:
app/Providers/AppServiceProvider.php
<?php
namespace App\Providers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
class AppServiceProvider extends ServiceProvider
{
public function boot(): void
{
$this->removePublicPHPFromURL();
}
protected function removePublicPHPFromURL()
{
if (Str::contains(request()->getRequestUri(), '/public/')) {
$url = str_replace('public/', '', request()->getRequestUri());
if (strlen($url) > 0) {
header("Location: $url", true, 301);
472
exit;
}
}
}
}
O si quieres para remover el index.php, queda como:
protected function removeIndexPHPFromURL()
{
if (Str::contains(request()->getRequestUri(), '/index.php/')) {
$url = str_replace('index.php/', '', request()->getRequestUri());
if (strlen($url) > 0) {
header("Location: $url", true, 301);
exit;
}
}
}
En ambos casos, como puedes ver, simplemente verificamos mediante el request, si existe el archivo de
index.php o la carpeta public y la removemos, luego, hacemos una redirección de tipo 301 que significa que
indica que es una redirección permanente a la misma URL quitando la carpeta/archivo public y/o index.php.
Queues and Job / Colas y Trabajos
Laravel al permitir poder hacer toda clase de sistemas desde sencillos a complejos, muchas veces estas tareas
pasando por un alto computo, y es una de las desventajas principales que tenemos en las aplicaciones web con
respecto a las aplicaciones de escritorio, y es que las aplicaciones web, usualmente (las tradicionales al menos)
tienen menos recursos de cómputo disponibles, o son más limitados y esto es algo que vemos en hacer
operaciones sencillas como enviar un correo o procesar una imagen, que al emplear el hilo principal del servidor
(el mismo que empleamos a momento de hacer peticiones al servidor) vemos que el servidor tarda unos pocos
segundos en devolver una respuesta y es este tiempo adicional el empleado para hacer estas tareas completas.
Adicionalmente, si la operación a realizar supera la capacidad de cómputo o muchas personas al mismo tiempo
realizan operaciones de este tipo, el servidor puede dar un error de agotamiento del tiempo (time exhaustion).
Por lo comentado en el anterior párrafo, es que debemos de emplear un mecanismo para poder hacer todos
estos procesos de manera eficiente, y la forma que tenemos de hacer esto, es delegando estas operaciones, en
vez de hacerlas el hilo principal, podemos. Enviarle estas tareas a un proceso (o varios dependiendo de cómo lo
configuremos) el cual se encarga de ir procesando tareas de manera consecutiva una detrás de la otra y con
esto, ya introducimos la definición de colas y trabajos.
Los trabajos son estas operaciones costosas a nivel de cómputo, enviar un correo, procesar una imagen,
procesar un archivo excel, generar un pdf, etc, los cuales son asignados o enviados a una cola o colas
(dependiendo de cómo lo configuremos) y de van procesando de manera eficiente es decir, podemos habilitar
una cantidad determinada de procesos secundarios que son manejados por las colas para procesar estas tarea
por lo tanto, con este sistema, no importa cuantos trabajos existan en un mismo tiempo, que se irán procesando
473
de a poco sin saturar el sistema. Adicionalmente, es posible especificar prioridades en los trabajos para que se
ejecuten antes que otros.
En definitiva, al emplear el sistema de colas y trabajadores, se mejora la capacidad de respuesta, es posible
incrementar de manera horizontal la cantidad de trabajadores disponibles para procesar estos trabajos, es
posible volver a ejecutar los trabajos fallidos, dando con esto una mejor tolerancia a fallos al sistema, todo esto,
de manera asíncrona sin afectar al hilo principal
Así que, aclarado la importancia de este sistema, vamos a conocer cómo implementarlo.
Controlador de cola
Primero, debemos de elegir un controlador de colar para emplear entre todos los existentes:
● 'sync': El controlador 'sync' ejecuta los trabajos en cola justo después y en el mismo ciclo
solicitud-respuesta. Es adecuado para entornos de desarrollo y pruebas, pero no para producción.
● 'database: el controlador de 'base de datos' almacena los trabajos en cola en una tabla de base de datos
en un proceso de trabajo de cola independiente.
● 'redis': el controlador 'redis' utiliza Redis como almacén de cola.
● 'beanstalkd': El controlador 'beanstalkd' utiliza el servicio de cola Beanstalkd, para procesar las colas.
● 'sqs' (Amazon Simple Queue Service): el controlador 'sqs' se utiliza para la integración con Amazon SQS.
● 'null': el controlador nulo se utiliza para desactivar el sistema de colas por completo.
Por defecto, está configurado el de la base de datos:
config\queue.php
'default' => env('QUEUE_CONNECTION', 'database')
Y para realizar las siguientes pruebas, puedes emplear el de database aunque, usualmente Redis es una
excelente opción por ser una base de datos rápida y eficiente y que instalamos anteriormente y es la que
usaremos:
config\queue.php
'default' => env('QUEUE_CONNECTION', 'redis')
Finalmente, iniciamos el proceso para ejecutar los trabajos mediante:
$ php artisan queue:work
Y veremos por la consola:
INFO Processing jobs from the [default] queue.
Cada vez que se procesa un trabajo, verás por la terminal:
.................................................................... 3s DONE
474
2024-07-12 09:44:31 App\Jobs\TestJob
............................................................................... RUNNING
2024-07-12 09:44:34 App\Jobs\TestJob
............................................................................... 3s DONE
2024-07-12 09:45:43 App\Jobs\TestJob
............................................................................... RUNNING
No importa si desde tus controladores o similares despachas trabajos y la cola NO está activa, Laravel las
registras y cuando actives el proceso de la cola, las despacha; y esto es todo, ya con esto Laravel levanta un
proceso para gestionar los trabajos, falta crear el trabajo que veremos en el siguiente apartado.
Creación y envío de jobs/trabajos
En Laravel, un trabajo es una unidad de trabajo que se puede poner en una cola para su posterior ejecución
como hablamos anteriormente y de esta forma, sacar esta unidad de trabajo del ciclo de petición/respuesta.
Para crear una tarea, tenemos el siguiente comando:
$ php artisan make:job <MyJob>
Vamos a crear un trabajo de ejemplo:
$ php artisan make:job TestJob
Para entender su funcionamiento, hagamos el siguiente ejemplo:
app\Jobs\TestJob.php
<?php
namespace App\Jobs;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
class TestJob implements ShouldQueue
{
use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
/**
* Create a new job instance.
*/
public function __construct()
475
{
//
}
/**
* Execute the job.
*/
public function handle(): void
{
Log::info('Before Sleep');
sleep(3);
Log::info('After Sleep');
}
}
Como puedes ver, simplemente coloca dos mensajes por el log y dormimos el hilo entre cada mensaje por un
tiempo determinado de 3 segundos.
Desde algún controlador, componente o ruta, indicamos en base a alguna lógica cuando queremos colocar este
trabajo a procesar, en este ejemplo, en una ruta:
routes\web.php
Route::get('test-job',function(){
TestJob::dispatch();
return 'Super vista';
});
Y al ingresar a la ruta, veremos que apenas al ingresar a la misma, aparece un mensaje en el archivo de logs,
segundos después, aparece otro mensaje, para apreciar este ejemplo de manera efectiva, se recomienda al
editor que coloque el log justamente al lado del navegador y vea en tiempo real el ejercicio.
Y es básicamente esto, claro está, que el trabajo es una operación costosa que en este ejemplo es simulada
durmiendo al hilo por 3 segundos, como comentamos antes, esta operación costosa puede ser enviar un correo,
procesar algún archivo, conectarse a una API, entre otras.
Trabajo para procesar emails
Como ejemplos finales, creamos un trabajo para enviar correos:
$ php artisan make:job SendWelcomeEmail
$ php artisan make:mail WelcomeEmail
Con el contenido para enviar un correo; definimos el trabajo:
app\Jobs\SendWelcomeEmail.php
476
<?php
namespace App\Jobs;
use App\Mail\WelcomeEmail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
class SendWelcomeEmail implements ShouldQueue
{
use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
public $user;
public function __construct(User $user)
{
$this->user = $user;
}
public function handle(): void
{
$r = rand(1, 3);
Log::error($r);
sleep(2);
if ($r > 2) {
// 3
Mail::to($this->user->email)->send(new WelcomeEmail($this->user));
} else {
// 1 2
Mail::to($this->user->email)->send(new WelcomeEmail());
}
}
}
El email queda como:
app\Mail\WelcomeEmail.php
<?php
477
namespace App\Mail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
class WelcomeEmail extends Mailable
{
use Queueable, SerializesModels;
public $user;
public function __construct(User $user)
{
$this->user = $user;
}
public function envelope(): Envelope
{
return new Envelope(
subject: 'Welcome Email',
);
}
public function content(): Content
{
return new Content(
view: 'emails.welcome',
);
}
}
La vista:
resources\views\emails\welcome.blade.php
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="ie=edge">
478
<title>Document</title>
</head>
<body>
<h1>Welcome Email!</h1>
<p>Hi {{$user->email}}</p>
</body>
</html>
Y la ruta para probar:
routes\web.php
Route::get('test-welcome-user',function(){
SendWelcomeEmail::dispatch(User::first());
return 'User welcome';
});
Si no tienes el driver de mail configurado, el driver empleado por defecto es el de log:
storage\logs\laravel.log
Y verás en el log el cuerpo del email enviado.
Trabajo para procesar imagen
Y otro para procesar una imagen:
$ php artisan make:job ProcessImage
Con el siguiente contenido:
app\Jobs\ProcessImage.php
<?php
namespace App\Jobs;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
479
class ProcessImage implements ShouldQueue
{
use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
/**
* Create a new job instance.
*/
public $image;
public function __construct(string $image)
{
$this->image = $image;
}
/**
* Execute the job.
*/
public function handle(): void
{
$manager = new ImageManager(new Driver());
$image = $manager->read(public_path($this->image));
$image->scale(width:50);
$image->toWebp()->save(public_path("uploads\posts\\").time().'.webp');
}
routes\web.php
Route::get('/image', function () {
ProcessImage::dispatch('uploads\posts\test.png');
return 'image view';
});
El el método anterior, empleamos el paquete de:
https://image.intervention.io/v3
$ composer require intervention/image
En el cual, puedes ver el proceso para escalar una imagen que consiste en, crear el driver o manejador,
referenciar la imagen desde el public_path(), realiza las operaciones sobre la imagen, en este ejemplo una
escala de 50 pixeles y finalmente guarda en un formato webp; puedes revisar la documentación oficial para más
detalles.
480
Otros comandos y opciones útiles
El uso de las colas y los trabajos es bastante flexible, como comentamos anteriormente, tenemos diferentes
opciones para personalizar la misma, como indicar las veces que debe de realizar una operación fallida:
Cantidad de intentos cuando falla el trabajo:
$ php artisan queue:work --queue=high_priority_queue --tries=2
Indicar el driver:
$ php artisan queue:work --queue=beanstalkd
Y al ejecutar, veríamos el driver establecido:
Processing jobs from the [beanstalkd] queue.
Trabajos fallidos
En el siguiente ejemplo, simularemos algunos errores para que, podamos ver el funcionamiento de cómo
procesar una cola fallida, colocaremos que la cola intente 3 veces por trabajos fallidos:
$ php artisan queue:work --queue=high_priority_queue --tries=3
Para simular los trabajos fallidos, usaremos un número aleatorio entero entre 1 y 3:
rand(1, 3)
En el cual, si es un número entre 1 y 2, entonces hacemos una excepción en el trabajo (instanciar la clase
WelcomeEmail() sin el argumento del usuario) y el trabajo falla:
app\Jobs\SendWelcomeEmail.php
<?php
namespace App\Jobs;
use App\Mail\WelcomeEmail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
class SendWelcomeEmail implements ShouldQueue
481
{
use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
public $user;
public function __construct(User $user)
{
$this->user = $user;
}
public function handle(): void
{
$r = rand(1, 3);
Log::error($r);
sleep(2);
if ($r > 2) {
// 3
Mail::to($this->user->email)->send(new WelcomeEmail($this->user));
} else {
// 1 2
Mail::to($this->user->email)->send(new WelcomeEmail());
}
}
}
Al momento de ejecutar el comando anterior, veras salidas como la siguiente:
2024-07-15 10:03:35 App\Jobs\SendWelcomeEmail
...................................................................... RUNNING
2024-07-15 10:03:37 App\Jobs\SendWelcomeEmail
...................................................................... 2s FAIL
2024-07-15 10:03:37 App\Jobs\SendWelcomeEmail
...................................................................... RUNNING
2024-07-15 10:03:39 App\Jobs\SendWelcomeEmail
...................................................................... 2s FAIL
2024-07-15 10:03:39 App\Jobs\SendWelcomeEmail
...................................................................... RUNNING
2024-07-15 10:03:41 App\Jobs\SendWelcomeEmail
...................................................................... 2s DONE
Al ser un proceso aleatorio, a veces procesa la cola a la primera o a veces ejecutará los 3 intentó de manera
fallida o como en el ejemplo anterior, fallaron 2 y pasó la última.
Atributos de la clase Job
Tenemos distintos atributos para personalizar el comportamiento de los jobs a nivel de atributos de la clase; entre
los más importantes:
482
● $tries, indica la cantidad de intentos para ejecutar el job, si se especifica el número máximo de intentos en
el trabajo, tendrá prioridad sobre el valor --tries.
● $maxExceptions, indica la cantidad de excepciones que pueden ocurrir antes de que el trabajo falle.
● $failOnTimeout, indica la cantidad de tiempo que se le dará al job para indicar un job fallido.
● $deleteWhenMissingModels, indica a Laravel que descarte el job en caso de que falle.
Por ejemplo, para indicar que intente hasta 5 veces un trabajo fallido:
<?php
namespace App\Jobs;
class ProcessPodcast implements ShouldQueue
{
/**
* The number of times the job may be attempted.
*
* @var int
*/
public $tries = 5;
}
Más información en:
https://laravel.com/docs/master/queues
Manejo de páginas de errores y excepciones
En este apartado, veremos cómo podemos capturar excepciones de diversos tipos y realizar algún procedimiento
adicional y también cómo podemos personalizar las páginas de errores, en definitiva, veremos cómo podemos
personalizar el comportamiento al ocurrir errores en nuestra aplicación.
Manejo de excepciones
En este apartado, vamos a conocer algunos scripts para poder personalizar el manejo de las excepciones,
realmente podemos realizar varias operaciones como puedes ver en la documentación oficial:
https://laravel.com/docs/master/errors
Pero, vamos a ver algunos que podemos considerar los más utilizados.
En un proyecto en Laravel, tenemos el archivo bootstrap/app.php en el cual podemos centralizar las excepciones
de la siguiente manera.
bootstrap/app.php
483
En este primer ejemplo, podemos ver cómo podemos capturar una excepción personalizada y luego hacer algo
en este caso ese algo es devolver una página personalizada para tal excepción o una respuesta JSON si la
petición viene del api (Api Rest):
bootstrap/app.php
use App\Exceptions\InvalidOrderException;
use Illuminate\Http\Request;
->withExceptions(function (Exceptions $exceptions) {
$exceptions->render(function (NotFoundHttpException $e, Request $request) {
// dd(auth()->user());
if ($request->is('api/*')) {
return response()->json(['message' => '404']);
} else {
return response()->view('errors.NotFoundHttpException');
}
});
})->create();
})
La vista puede tener cualquier estilo o formato:
resources\views\errors\NotFoundHttpException.blade.php
<!DOCTYPE html>
<html lang="en">
<head>
</head>
<body>
<h1>No found!!</h1>
</body>
</html>
No solamente podemos preguntar por la excepción, también tenemos acceso a código de estado mediante la
respuesta y desde aquí retornar una página, JSON o redirección:
bootstrap/app.php
use Symfony\Component\HttpFoundation\Response;
->withExceptions(function (Exceptions $exceptions) {
$exceptions->respond(function (Response $response) {
if ($response->getStatusCode() === 419) {
return back()->with([
484
'message' => 'The page expired, please try again.',
]);
}
return $response;
});
})
Personalización de páginas de error
Podemos personalizar fácilmente páginas como la de 404 o 500 creándose en la siguiente carpeta:
resources/views/errors/
Colocando el nombre del código de estado como nombre de la vista; por ejemplo:
resources/views/errors/404.blade.php
resources/views/errors/500.blade.php
Con contenidos como el siguiente:
resources/views/errors/404.blade.php
@extends('layouts.app')
@section('content')
<div class="container">
<h1>404 Not Found</h1>
<p>Sorry, the page you are looking for does not exist.</p>
</div>
@endsection
resources/views/errors/500.blade.php
@extends('layouts.app')
@section('content')
<div class="error-page">
<h1>500 Internal Server Error</h1>
<p>Please try again later.</p>
<a href="{{ url('/') }}">Go back to the home page</a>
</div>
@endsection
La página de error 500 solamente aparece cuando desactivamos el modo debug en Laravel.
485
Excepciones personalizadas
Crear una excepción personalizada en Laravel es particularmente útil para poder personalizar las excepciones al
monumento de hacer ciertas operaciones según la lógica de negocio de tu aplicación; si creas una orden y tiene
un formato invalido, un registro no encontrado, etc; por ejemplo, si no se encuentra un recurso:
app/Exceptions/ResourceNotFoundException.php
namespace App\Exceptions;
use Exception;
class ResourceNotFoundException extends Exception
{
// custom properties or methods...
}
Y para emplearla, usamos el throw para lanzar la excepción:
use App\Models\User;
use App\Exceptions\ResourceNotFoundException;
public function getUserById($id)
{
$user = User::find($id);
if (!$user) {
throw new ResourceNotFoundException("User with ID $id not found.");
}
return $user;
}
Estrangulamiento/Throttling
En este apartado aprenderemos a limpiar la cantidad de solicitudes que puede realizar un cliente, lo cual, es
particularmente útil para una Rest API, al momento de realizar consultas a la misma o nuestra aplicación web,
con esto, podemos garantizar que no existan abusos en los cuales un usuario pueda realizar múltiples solicitudes
en un cortó periodo de tiempo, por ejemplo:
Route::middleware('throttle:60,1')->group(function () {
// Your routes Here
});
Con el código anterior, estamos indicando mediante el parámetro 60 y 1 que en un plazo de 1 minuto el usuario
puede enviar 60 peticiones.
Subdominios o múltiples dominio
En Laravel, tenemos una característica muy interesante que nos permite definir a nivel de rutas, que solamente
puedan ser procesadas mediante un subdominio o su dominio en particular:
Route::domain('academy.desarrollolibre.net')->group(function () {
486
Route::get('***', [***Controller::class, 'index']);
});
Como puedes apreciar, es solamente un agrupado, esto es particularmente útil para crear subdominios que
tengan un propósito en particular, uno de dashboard:
Para el módulo de dashboard:
Route::domain('admin.desarrollolibre.net')->group(function () {
Route::get('***', [***Controller::class, 'index']);
});
Esto es aplicado para un dominio de igual forma:
Route::domain('desarrollolibre.net')
En las rutas, también podemos crear funciones y/o condicionales que agrupan un conjunto de rutas:
function routesBlog()
{
Route::group(['prefix' => 'blog'], function () {
Route::get('',
[App\Http\Controllers\Blog\PostController::class,'index'])->name('web-post-list');
});
}
if (config('app')['app_route'] == 'production') {
Route::domain('academy.desarrollolibre.net')->group(function () {
Route::get('***', [***Controller::class, 'index']);
});
}
Y de esta forma, podemos tener más organizadas las rutas que tengan un determinado propósito, como las rutas
para el dashboard, blog, etc.
Descargar archivos protegidos
La descarga de archivos es una característica común en el desarrollo de software, poder permitir la descarga de
determinados archivos en base a algún control interno determinado por las reglas de negocio de tu aplicación, es
un proceso común, por ejemplo, la venta de archivos que tengas alojados en la aplicación y que una vez
adquirido por el usuario, el mismo puede descargarlo, para eso, desde la aplicación, verificamos el pago y a
posterior permitimos la descarga.
Lo importante de notar aquí es que, los archivos no pueden estar alojados en la aprieta public, como si fueran
una imagen cargada por el proceso de upload como hicimos antes, ya que,cualquier persona que sepa cual es el
nombre del archivo pudiera acceder a él.
487
Recordemos que en un proyecto en Laravel, la única carpeta que es accedida de manera pública es justamente
la carpeta public, por lo tanto, para estos archivos que queramos controlar el acceso no es recomendado emplear
esta carpeta.
Podemos subir archivos en cualquier carpeta de la aplicación, no solamente la carpeta public, lo cual es
particularmente útil para estos escenarios en donde queremos controlar el acceso a estos archivos, por ejemplo,
la carpeta de storage; creamos un disco en consecuencia:
config\filesystems.php
'files_sell_uploads' => [
'driver' => 'local',
'root' => app()->storagePath()
],
La carga del archivo sería algo como:
function uploadBook()
{
$this->rules = [
'fileBook' => 'nullable|mimes:epub,pdf|max:20024'
];
$this->validate();
if ($this->fileBook) {
$name = time() . '.' . $this->fileBook->getClientOriginalExtension();
$this->fileBook->storeAs('book', $name, 'files_sell_uploads');
YourModel::create([
'file' => $name,
'type' => $this->fileBook->getClientOriginalExtension(),
***
]);
}
}
Y en este ejemplo, te muestro en base a alguna condición que debe de cumplir el usuario:
if ($filePayment && $file)
Y poder descargar el archivo:
Storage::disk('files_sell_uploads')->download('book/' . $file->file, "book." .
$file->type);
488
Es importante aclarar que la única forma de acceder a estos archivos mediante el canal http seria mediante la
función anterior, al ser la carpeta de storage una carpeta que no se puede acceder de manera pública, la única
forma que tiene un usuario para acceder a estos archivos es que nosotros implementemos una capa de acceso
como la función anterior.
En definitiva, el esquema presentado anteriormente es estupendo si quieres desarrollar alguna tienda en línea
sobre tu aplicación en donde los archivos a vender se encuentren almacenados en la misma aplicación.
public function downloadFile(File $file) //show
{
$user = auth()->user() ?? auth('sanctum')->user();
$filePayment = FilePayment::where(***)->first();
$file = File::where(***)->first();
if ($filePayment && $file) {
// return Storage::disk('files_sell_uploads')->download('book/1724355661.pdf');
return Storage::disk('files_sell_uploads')->download('book/' . $file->file,
"book." . $file->type);
}
return response()->errorResponse("", 403, 'Producto no adquirido o no existe');
}
Preferencias de usuarios
Podemos crear un esquema fácilmente una columna para manejar todo tipo de preferencias de usuario como el
lenguaje, modo oscuro, o cualquier otro que requiera tu aplicación; aunque podríamos crear una tabla asociada o
columnas en la tabla de usuario, opción que es completamente válida, en este apartado, vamos a ver una opción
más flexible ya que las preferencias de usuario son tan flexible que en cualquier momento podemos requerir
crear una, una vez implementada el esquema presentado en este apartado, verás que es mucho más rápido que
estar creando migraciones, aplicando las migraciones, modificando modelos, etc.
Para manejar las preferencias de usuario, solamente vamos a necesitar una sola columna, que en este ejemplo
es el de extra, el cual, debes de crear mediante migraciones y demás pasos; este campo vamos a manejar un
json u objeto en JavaScript con el cual, pondremos todas las preferencias de usuario:
$table->string('extra')->nullable()->default('');
En la siguiente función, puedes ver que se administran todas las preferencias de usuario y mediante el objeto de
request, se verifica cual es la preferencia de usuario recibida:
function setExtra()
{
$user = Auth::user();
489
// si $user->extra tiene algun valor invalido, restablece a un objeto limpio
$extra = json_decode($user->extra ?? "{}") ?? json_decode("{}");
if (request("autoplay")) {
$extra->autoplay = request("autoplay") == 1 ? true : false;
}
if (request("darkmode")) {
$extra->darkmode = request("darkmode") == 1 ? true : false;
}
$user->update([
'extra' => json_encode($extra)
]);
}
Primero traemos lo que existe en el campo de extra desde la base de datos:
$extra = json_decode($user->extra ?? "{}") ?? json_decode("{}");
A partir de este, se establecen los valores en base a las preferencias de usuario:
if (request("darkmode")) {
$extra->darkmode = request("darkmode") == 1 ? true : false;
}
Que pueden ser de cualquier tipo como textos o booleanos, en el ejemplo anterior, sería un booleano: finalmente,
se establece el objeto convertido a un json:
$user->update([
'extra' => json_encode($extra)
]);
Bannear Usuarios
Hay varias formas de poder implementar el baneo de usuarios, podemos usar un paquete como:
https://github.com/cybercog/laravel-ban
Aunque, podemos implementarlo fácilmente con una columna adicional:
database/migrations/0001_01_01_000000_create_users_table.php
Schema::create('users', function (Blueprint $table) {
***
$table->enum('banned', ['yes', 'not'])->default('not');
});
490
// ALTER TABLE `users`
ADD COLUMN `banned` ENUM('yes','not') NULL DEFAULT 'not' AFTER `block_shopping`;
Y un middleware que se ejecute después de hacer el login, y si el usuario está baneado, cierras la sesión y
redireccionar con un mensaje que indique por ejemplo, que está baneado:
app/Http/Middleware/CheckBanned.php
<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Session;
use Symfony\Component\HttpFoundation\Response;
class CheckBanned
{
public function handle(Request $request, Closure $next)
{
if (auth()->check() && auth()->user()->banned == "yes") {
auth()->guard('web')->logout();
Session::flush();
return redirect()->route('login')->with('status', 'Your account has been
suspended. Please contact administrator.');
}
return $next($request);
}
}
Y desde las rutas que requieran de que el usuario esté autenticado, definimos el middleware:
routes/web.php
Route::group(['middleware' => [CheckBanned::class, 'auth:sanctum',]], function () {
// *** YOUR ROUTES
});
Desde la vista de login, imprimimos el mensaje:
resources/views/auth/login.blade.php
491
***
@session('status')
<div class="mb-4 font-medium text-sm text-green-600 dark:text-green-400">
{{ $value }}
</div>
@endsession
Código fuente del capitulo:
https://github.com/libredesarrollo/book-course-laravel-base-generals-11
Capítulo 20: Paquetes imprescindibles
En este capítulo listaremos y veremos el funcionamiento de algunos paquetes que seguramente te podrán servir
en más de una situación en la cual quieras implementar alguna funcionalidad específica como generar QRs,
generar Excels, entre otros.
Simple QR
Los códigos QRs están de moda y sirven para poder extender nuestra aplicación de una manera sencilla y visual,
compartiendo un código QR que internamente puede procesar la aplicación mediante algún código de descuento
o URL, podemos emplear el siguiente paquete:
https://github.com/SimpleSoftwareIO/simple-qrcode
Para instalarlo:
$ composer require simplesoftwareio/simple-qrcode
Veamos algunos ejemplos de como usarlo:
QrCode::format('svg')->size(700)->color(255,0,0)->generate('Desarrollo libre Andres',
'../public/qrcodes/qrcode.svg');
QrCode::format('png')->size(700)->color(255, 0, 0)->merge('/assets/img/logo.png', .3,
true)->generate('Desarrollo libre Andres', '../public/qrcodes/qrcode.png');
492
Como puedes ver, como los parámetros anteriores podemos indicar aspectos como el formato, tamaño, color y
contenido del QR que luego es generado en alguna ubicación de la aplicación, en este ejemplo, dentro de la
carpeta public.
Laravel Excel
Si la aplicación que estás desarrollando requiere de emplear hojas de cálculo como excel, el siguiente paquete:
https://laravel-excel.com/
Instalamos mediante:
$ composer require maatwebsite/excel
El paquete consta de dos partes, para importar o leer excels para hacer alguna operación a nivel de la aplicación
como el llenado de una tabla y exportar datos mediante el formato de Excel.
Exportar
Tenemos un comando que podemos ejecutar para generar la clase que se encargaría de exportar un conjunto de
datos a formato XSLR, indicamos el modelo con el cual va a generar los datos:
$ php artisan make:export PostsExport --model=Post
En este ejemplo, colocamos una consulta que retorna todos los posts:
app\Exports\PostsExport.php
<?php
namespace App\Exports;
use App\Models\Post;
use Maatwebsite\Excel\Concerns\FromCollection;
class PostsExport implements FromCollection
{
/**
* @return \Illuminate\Support\Collection
*/
public function collection()
{
return Post::all();
}
}
Puedes personalizar la consulta a tu gusto; para usarlo:
493
public function export(){
return Excel::download(new PostsExport, 'posts.xlsx');
}
Importar
Al igual que para exportar, tenemos un comando para importar:
$ php artisan make:import PostsImport --model=Post
Como respuesta, tenemos un row que equivale a una fila del excel que podemos emplearla para construir una
instancia del modelo:
app\Imports\PostsImport.php
<?php
namespace App\Imports;
use App\Models\Post;
use Maatwebsite\Excel\Concerns\ToModel;
class PostsImport implements ToModel
{
/**
* @param array $row
*
* @return \Illuminate\Database\Eloquent\Model|null
*/
public function model(array $row)
{
// dd($row[3]);
return new Post([
// 'id' => $row[0],
'title' => $row[1],
'slug' => $row[2],
'content' => $row[3],
'description' => $row[4],
// 'image' => $row[5],
'posted' => $row[6],
'category_id' => $row[7],
'user_id' => 1,
]);
}
}
494
Para usarlo, puedes emplear el mismo archivo de excel generado antes y lo ubicas en la carpeta de public:
public function import(){
Excel::import(new PostsImport, 'posts.xlsx');
return "Import!";
}
SEO en Laravel
Existen múltiples paquetes para generar etiquetas SEO para Laravel, esto es estupendo ya hay ciertas partes de
la aplicación que es necesario devolver algunas etiquetas tipo met justamente donde lo necesitamos, sin
preocuparnos de si los nombres de estas etiquetas son correctas, o no, ya las genera el paquete por nosotros:
SEOTools
Este es un paquete que permite generar etiquetas meta fácilmente mediante el uso de métodos:
https://github.com/artesaos/seotools
$ composer require artesaos/seotools
Con esto, ya podemos emplear métodos para generar las etiquetas como:
SEOTools::setTitle("Latest posts");
SEOTools::opengraph()->addProperty('type', 'articles');
SEOTools::twitter()->setSite('@LibreDesarrollo');
SEOTools::jsonLd()->addImage(URL::to('/public/images/logo/logo.png'));
SEOTools::setDescription("Here you will find the latest posts that I have uploaded to my
blog.");
Desde la vista:
{!! SEO::generate() !!}
<!-- MINIFIED -->
{!! SEO::generate(true) !!}
Y tenemos:
<title>Latest posts</title>
<meta name="description" content="Here you will find the latest posts that I have uploaded
to my blog.">
<meta property="og:title" content="Latest posts"><meta property="og:type"
content="articles">
<meta property="og:description" content="Here you will find the latest posts that I have
uploaded to my blog.">
495
<meta name="twitter:title" content="Latest posts"><meta name="twitter:site"
content="@LibreDesarrollo">
<meta name="twitter:description" content="Here you will find the latest posts that I have
uploaded to my blog.">
<script
type="application/ld+json">{"@context":"https://schema.org","@type":"WebPage","name":"Últim
as publicaciones","description":"Here you will find the latest posts that I have uploaded
to my blog."}</script>
Para personalizar el paquete como valores por defecto o sufijos en el título, debes de publicar el archivo de
configuración:
$ php artisan vendor:publish
--provider="Artesaos\SEOTools\Providers\SEOToolsServiceProvider"
Laravel SEO
Este paquete genera metaetiquetas empleadas para el SEO:
● Etiqueta de title (con sufijo para todo el sitio)
● Metaetiquetas (author, description, image, robots, etc.)
● Etiquetas OpenGraph (Facebook, LinkedIn, etc.)
● Etiquetas de Twitter
● Datos estructurados (artículo y ruta de navegación)
● Favicon
● Etiqueta de robots
https://github.com/ralphjsmit/laravel-seo
$ composer require ralphjsmit/laravel-seo
Con esto, ya podemos emplear métodos para generar las etiquetas como:
$post = Post::find(1);
$post->seo->update([
'title' => 'My great post',
'description' => 'This great post will enhance your live.',
]);
Desde la vista:
{!! seo()->for($post) !!}
Hay varias otras configuraciones que puedes encontrar en la documentación oficial.
496
Laravel Dashboard
Con este paquete podemos instalar un Dashboard para Laravel (pendiente)
Laravel Nocapcha
Para generar captcha (pendiente)
Laravel Debugbar
Este paquete es un imprescindible al momento de desarrollar nuestra aplicación en Laravel, desde el mismo,
podemos apreciar distintos datos como que consultas a la base de datos se están realizando y en qué cantidad,
tiempo de respuesta de servidor, entre otros; para ello, empleamos el siguiente paquete:
https://github.com/barryvdh/laravel-debugbar
$ composer require barryvdh/laravel-debugbar --dev
Una vez instalado, veremos:
Figura 20-1: DebugBar
Para habilitar o deshabilitar la barra, podemos emplear la variable de entorno (por defecto aparece habilitada):
DEBUGBAR_ENABLED
O
\Debugbar::enable();
\Debugbar::disable();
PayPal
PayPal es la billetera electrónica por excelencia y es muy utilizada para las compras en líneas, es decir, por
Internet y la podemos emplear muy fácilmente en cualquier proyecto web y en Laravel no es la excepción.
En este apartado vamos a conocer los pasos para configurar PayPal como método de pago en base a algún
producto que queramos vender.
497
Aunque no tenemos un paquete de PayPal exclusivo para Laravel, si tenemos una solución en JavaScript, la
cual, no requiere de emplear algún paquete exclusivo para PHP como veremos más adelante:
https://www.npmjs.com/package/@paypal/paypal-js
Lo primero que debemos de hacer es instalar el paquete mediante el siguiente comando, solo si quieres emplear
la opción de Node:
$ npm install @paypal/paypal-js
En el libro, usaremos la opción de la CDN.
Claves de acceso y usuarios de prueba
Para poder integrar PayPal en nuestra aplicación, debemos de tener una cuenta en PayPal, una vez conseguido,
si vamos al sitio de desarrollo de PayPal:
https://developer.paypal.com/home
Damos click sobre la opción que dice "API Credentials" y crear una aplicación, para ello, presionamos el botón de
"Create App" y aparecerá un diálogo como el siguiente:
498
Figura 20-2: Diálogo para crear una app en PayPal
Puedes dejar la opción de "Merchant" y "Create App".
Creada las credenciales para poder emplear la API de PayPal, aparecerán listadas en la parte de abajo:
499
Figura 20-3: Credenciales de PayPal
Tenemos una clave secreta que la usaremos en el servidor y una pública que la usaremos en el cliente, por lo
tanto, quedará expuesta para cualquier persona que vea el código fuente de la página desde su navegador, a su
vez, tenemos acceso a unas claves de prueba, con las cuales podemos ir realizando las peticiones a una cuenta
de prueba o sandbox.
Aparte de las claves, se generan usuarios de prueba que emplearemos para realizar la conexiones a la API de
pruebas de PayPal disponibles en "Sandbox accounts":
Figura 20-4: Cuenta de sandbox
En el servidor debemos de configurar las claves de acceso, como recomendación, define las de pruebas como
variables de entorno:
.env
PAYPAL_CLIENT_ID="<YOUR_DEV_PAYPAL_CLIENT_ID>"
PAYPAL_SECRET="<YOUR_DEV_PAYPAL_SECRET>"
Y en las configuraciones las de producción:
config\app.php
500
return [
***
'paypal_id' => env('PAYPAL_CLIENT_ID', "<YOUR_PRO_PAYPAL_CLIENT_ID>"),
'paypal_secrect' => env('PAYPAL_SECRET',"<YOUR_PRO_PAYPAL_SECRET>"
),
Implementar un sencillo sistema de pagos
Al emplear este paquete, tenemos que hacer desarrollos en ambos lados, en el cliente y en el servidor.,
comencemos por el cliente que es en donde hacemos la mayor configuración.
Cliente
En el cliente, comencemos creando un DIV que servirá como elemento contenedor para el widget de PayPal,
puedes colocar cualquier identificador que luego colocaremos para referenciar el elemento HTML:
<div id="paypalCard"></div>
La siguiente función permite crear el widget de PayPal, primero, obtenemos una referencia al API de PayPal que
usaremos para crear el widget mediante:
paypal = await loadScript
Esto lo tenemos que hacer si trabajamos con el paquete en Node y algunas opciones de la CDN.
El siguiente paso es crear la orden, existen muchos parámetros de configuración, pero, en este ejemplo
solamente usamos el monto:
{
amount: {
value: this.price,
},
},
Siguiendo con la implementación, tenemos el callback de onApprove() que se ejecutoria cuando el usuario da
clicks sobre el widget y autoriza el pago, en el mismo, tenemos referencia a la orden ya creada que consta de la
información del pago al igual que la información del cliente que luego utilizaremos en el servidor, por lo tanto, el
siguiente paso es pasar esta data al servidor para autorizarla (y es el el servidor donde se emplea la clave
privada), por ejemplo, usando una petición por axios:
onApprove: function (data, actions) {
// TODO send data.orderID to server
}
La implementación con la CDN de PayPal, tenemos dos formatos:
<!DOCTYPE html>
501
<html lang="en">
<head>
<title>Document</title>
src="https://unpkg.com/@paypal/paypal-js@8.0.0/dist/iife/paypal-js.min.js"></script>
</head>
<body>
<div id="paypalButtons"></div>
<script>
window.paypalLoadScript({
clientId: "{{config('app')['paypal_id']}}"
}).then((paypal) => {
paypal.Buttons().render("#paypalButtons");
});
</script>
</body>
</html>
O la siguiente que es la que vamos a usar:
resources\views\paypal.blade.php
<!DOCTYPE html>
<html lang="en">
<head>
<title>Document</title>
<script
src="https://www.paypal.com/sdk/js?client-id={{config('app')['paypal_id']}}"></script>
</head>
<body>
<div id="paypalButtons"></div>
<script>
paypal.Buttons({
createOrder: function(data, actions){
return actions.order.create({
purchase_units:[
{
amount: {
value:50
}
}
]
502
})
},
onApprove: function(data, actions){
// TODO send order to server
console.log(data.orderID)
axios.post('paypal-process-order/'+data.orderID)
}
}).render("#paypalButtons");
</script>
</body>
</html>
En el código anterior, enviamos el identificador de la orden mediante axios, para eso, empleamos vite
(recordemos que axios viene instalado por defecto en Laravel en el archivo resources\js\bootstrap.js).
Creamos el controlador y la ruta:
routes\web.php
Route::get('/paypal', [PaymentPaypalController::class, 'paypal']);
Route::post('/paypal-process-order/{order}', [PaymentPaypalController::class,
'paypalProcessOrder']);
app\Http\Controllers\PaymentPaypalController.php
<?php
namespace App\Http\Controllers;
class PaymentPaypalController extends Controller
{
public function paypal() {
return view('paypal');
}
function paypalProcessOrder(string $order) {
dd($order);
}
}
En el controlador puedes ver que hacemos un sencillo condicional para siempre emplear las claves de acceso en
producción de PayPal cuando estamos en producción o las de desarrollo cuando estamos en desarrollo.
En caso de que quieras emplear Node, el código, queda como:
function setPaypal() {
503
let paypal;
try {
paypal = await loadScript({
"client-id":{{ config('app')['paypal_id'] }},
});
} catch (error) {
console.error("failed to load the PayPal JS SDK script", error);
}
if (paypal) {
try {
await paypal
.Buttons({
createOrder: function (data, actions) {
// This function sets up the details of the transaction, including the amount
and line item details.
return actions.order.create({
purchase_units: [
{
amount: {
value: this.price,
},
},
],
});
}.bind(this),
onApprove: function (data, actions) {
// TODO send data.orderID to server
}.bind(this),
})
.render("#paypalCard");
} catch (error) {
console.error("failed to render the PayPal Buttons", error);
}
}
},
}
Es importante que notes que cuando estás en un ambiente de pruebas, la URL de PayPal indica que está en
modo sandbox:
504
Figura 20-5: Autenticación en Sandbox en PayPal
https://www.sandbox.paypal.com/checkoutnow?***
Y en producción luce de la siguiente manera:
https://www.paypal.com/checkoutnow?***
Con createOrder() creamos la orden, que se crea en base a los datos suministrados (el precio en este ejemplo
de 50$) y la autenticación del usuario, que indica que va a pagar la orden, con la función de onApprove() se
ejecuta cuando la orden ha sido aprobada y se pasa al servidor para su posterior completación.
505
Servidor
En el servidor solamente debemos de procesa la orden creada en el lado del cliente, es decir, de momento, lo
que hemos conseguido es crear la orden, con información del pago configurado por el cliente al aceptar el pago
mediante los pasos establecidos en la imagen anterior, pero, todavía la orden no ha sido procesada, para
procesarla, debemos de realizar una conexión HTTP con la API de PayPal, tenemos dos URLs, una de prueba:
https://api-m.sandbox.paypal.com
Y otra de producción:
https://api-m.paypal.com
Para el siguiente ejercicio, supondremos que cuando estamos en modo de desarrollo, usaremos el modo
sandbox de PayPal, para eso, usamos las configuraciones:
class PaymentPaypalController extends Controller
{
private $clientId;
private $secret;
private $baseURL = 'https://api-m.paypal.com';
// private $baseURL = 'https://api-m.sandbox.paypal.com';
public function __construct()
{
$this->baseURL =
config('app')['env'] == 'local' ? 'https://api-m.sandbox.paypal.com' :
'https://api-m.paypal.com';
$this->clientId =
config('app')['paypal_id'];
$this->secret =
config('app')['paypal_secrect'];
}
}
Pero, puedes crear una configuración específica para esto si no quieres que este sea el funcionamiento.
El siguiente paso consiste en realizar la comunicación, para esto, tenemos el siguiente script:
app\Http\Controllers\Api\PaymentPaypalController.php
<?php
namespace App\Http\Controllers\Api;
506
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
class PaymentPaypalController extends ApiResponseController
{
private $clientId;
private $secret;
private $baseURL = 'https://api-m.paypal.com';
// private $baseURL = 'https://api-m.sandbox.paypal.com';
public function __construct()
{
$this->baseURL = config('app')['env'] == 'local' ?
'https://api-m.sandbox.paypal.com' : 'https://api-m.paypal.com';
$this->clientId = config('app')['paypal_id'];
$this->secret = config('app')['paypal_secrect'];
}
private function paypalProcessOrder($order)
{
try {
$accessToken = $this->getAccessToken();
$response = Http::acceptJson()->withToken($accessToken)->withHeaders([
'Content-Type' => 'application/json'
])
->post($this->baseURL . "/v2/checkout/orders/$order/capture", [
'application_context' => [
'return_url' => "<URL-RETURN>",
'cancel_url' => "<URL-CANCEL>"
]
])->json();
return $response;
} catch (Exception $e) {
return $e;
}
return $response;
}
private function getAccessToken()
{
507
$response = Http::asForm()->withHeaders([
'Accept' => 'application/json',
'Content-Type' => 'application/x-www-form-urlencoded',
])
->withBasicAuth($this->clientId, $this->secret)
->post($this->baseURL . '/v1/oauth2/token', [
'grant_type' => 'client_credentials'
])->json();
return $response['access_token'];
}
}
Para procesar la orden, es necesario generar un token de acceso de PayPal, y para eso es el método de
getAccessToken() en el cual se configuran la petición de tipo formulario para que podamos suministrar el
'grant_type' => 'client_credentials' que es solicitado por paypal para realizar la petición, finalmente, se indica
que responda en formato JSON para poder procesar la solicitud.
Finalmente, completamos la orden mediante la ruta de /v2/checkout/orders/$order/capture en el cual
suministramos el token de acceso.
Al hacer la petición de manera exitosa desde los botones de PayPal, desde la pestaña de network, verás una
respuesta como la siguiente (si la imprimes mediante un dd($response)):
array:6 [ // app\Http\Controllers\PaymentPaypalController.php:44
"id" => "0AB01377M7722750N"
"status" => "COMPLETED"
"payment_source" => array:1 [
"paypal" => array:5 [
"email_address" => "sb-rwplq1388482@personal.example.com"
"account_id" => "3C7RGDT4A44VU"
"account_status" => "VERIFIED"
"name" => array:2 [
"given_name" => "John"
"surname" => "Doe"
]
"address" => array:1 [
"country_code" => "ES"
]
]
]
Lo importante es notar el estatus de orden completada, y justamente en este punto es donde los ingresos se
acreditan a la cuenta de PayPal configurada.
508
Como puedes ver, para claridad del ejercicio, lo implementamos como un método privado que tiene solamente la
implementación que debes de realizar del lado de PayPal para procesar el pago, dando total libertad para
implementar el resto de la lógica del producto que estás vendiendo en otro método, por ejemplo:
public function processSuccessOrder(YourModel $yourModel, $orderPayPalId)
{
$response = $this->paypalProcessOrder($orderPayPalId);
if (isset($response['status']) && $response['status'] == 'COMPLETED') {
// success paypal
<YourModel>::<SomeCustomMethod>($response['id'], json_encode($response),
$response['purchase_units'][0]['payments']['captures'][0]['amount']['value'], ***);
} else if (isset($response['status'])) {
// error paypal
return $this->errorResponse("", 202, "A problem has occurred with your order, the
status is" . $response['status'] . " and the ID is " . $response['id']);
}
}
En el ejemplo anterior, puedes ver que se empleó el método implementado anteriormente paypalProcessOrder()
para procesar exclusivamente la orden de PayPal (que pudieras pasarlo a un método privado para que
solamente sea consumido desde el controlador y no como una ruta), entonces, en este método implementamos
la lógica adicional para registrar el producto una vez hecho el pago, es decir, una vez verificado el pago
(isset($response['status']) && $response['status'] == 'COMPLETED') registramos la compra al cliente.
También en el ejemplo anterior, manejamos un else, que resulta cuando ocurre un problema al procesar la orden
por parte de PayPal que puede ser cualquier cosa, como un error de comunicación con PayPal con el banco (en
caso de que el cliente emplee una tarjeta), etc, manejamos un caso para mostrar el error al cliente, inclusive,
puedes imprimir todo el response provisto por PayPal:
json_encode($response)
Desde el apartado de desarrolladores de PayPal, desde la opción de "Api Calls", puedes seleccionar la aplicación
que estás empleando y ver las peticiones realizadas a dicha aplicación.
Finalmente, una vez terminada la implementación, puedes pasar a modo producción a PayPal cambiando la
configuración de APP_ENV a producción o crear una específica para PayPal.
Desde el cliente, podemos pasar más información que no sea solamente la orden de PayPal, por lo tanto, puedes
personalizarla según lo que estés vendiendo como mostramos en el fragmento de código anterior.
Finalmente, el plugin luce como:
509
Figura 20-6: PayPal botones cliente
Existen muchas opciones más para personalizar el widget anterior que puedes encontrar en la documentación
oficial:
https://www.npmjs.com/package/@paypal/paypal-js
https://developer.paypal.com/sdk/js/reference/
Extra: Stripe
Stripe es otra plataforma tipo billetera electrónica al igual que PayPal, podemos configurar pagos de servicios,
suscripciones entre otros y en este apartado, vamos a conocer cómo integrarlo en nuestra aplicación, en la
siguiente página:
https://stripe.com/
Puedes conocer todos los servicios que van desde pagos únicos, suscripciones, registro de clientes, emplear
otras plataformas como PayPal, Google Pay, Apple Pay, etc.
Stripe también puede ser implementada en un proyecto en Laravel de diversas maneras, mediante Vue, tenemos
un plugin:
https://vuestripe.com/
O Laravel Cashier (Stripe):
https://laravel.com/docs/master/billing
Que trataremos más adelante.
O mediante la SDK oficial:
https://docs.stripe.com/api
En ambos casos, debes de crear una cuenta en Stripe:
https://dashboard.stripe.com/register
510
Aparte de la cuenta, Stripe solicita datos y configuraciones como que agregues tu cuenta bancaria, activar el
modo de doble autenticación, etc.
Crear entorno de Prueba
Como primer paso, debemos de ir al siguiente enlace:
https://dashboard.stripe.com/test/dashboard
Click en la esquina superior derecha:
Figura 20-7: Opción de crear entorno de prueba en Stripe
Y crear el entorno de prueba:
511
Figura 20-8: Crear entorno de prueba en Stripe
Crear credenciales de prueba
Al igual que ocurre con PayPal, debemos de crear unas credenciales en Stripe para poder emplear; para ello,
vamos al siguiente enlace:
https://dashboard.stripe.com/test/dashboard
En el panel, verás una opción como la siguiente, en donde tienes acceso a las claves de la API:
Figura 20-9: Claves para la API
512
Para usar la aplicación en producción, lo único que debes de hacer es reemplazar las claves de pruebas a
producción, para ello, cambias a modo producción en la opción que tienes en el header de la web:
Figura 20-10: Modo prueba
Y reemplazas tus claves de prueba por las de producción que ahora aparecerán en Figura 20-9.
Crear productos de prueba
A diferencia de PayPal, debemos de crear los productos que vamos a vender para poder referenciales mediante
nuestra aplicación en Laravel/Vue mediante un identificador como veremos más adelante; para ello, desde el
siguiente enlace:
https://dashboard.stripe.com/test/products?active=true
Creamos al menos un producto desde el botón de "Crea un producto":
Figura 20-11: Producto de ejemplo
Desde el detalle del producto, en el precio configurado, al darle un click, tendrás el identificador del precio, que
usaremos más adelante para poder hacer una compra a este producto con el precio seleccionado:
513
Figura 20-12: Copiar ID del precio
Desde el botón de + de la imagen anterior, puedes crear más tarifas/precios ya que un producto puede tener de 1
a n pecios que son los que empleamos en nuestra aplicación para configurar los pagos; los pagos deben ser de
tipo puntual NO los recurrentes, los pagos recurrentes son para las suscripciones.
Vue Stripe
Esta es la forma más sencilla que tenemos para emplear Stripe junto con Vue, y ya sabemos que podemos
emplear fácilmente Vue en un proyecto en Laravel mediante la CDN, Node o Inertia, por lo tanto, veremos un
ejemplo de cómo crear un único pago con Stripe y Vue y más adelante, veremos cómo emplear Laravel Cashier
(Stripe) con el cual, podemos aprovechar todas las funcionalidades que nos ofrece Stripe desde un paquete
provista por Laravel.
Comencemos instalando Vue Stripe en nuestro proyecto mediante:
$ npm install @vue-stripe/vue-stripe
Y la forma recomendada, es emplearlo mediante un componente:
resources\js\vue\componets\stripe\OnePayment.vue
<template>
<div>
<stripe-checkout
ref="checkoutRef"
mode="payment"
:pk="publishableKey"
:line-items="lineItems"
:success-url="successURL"
514
:cancel-url="cancelURL"
@loading="v => loading = v"
/>
<button @click="submit">Pay now!</button>
</div>
</template>
<script>
import { StripeCheckout } from '@vue-stripe/vue-stripe';
export default {
components: {
StripeCheckout,
},
data () {
this.publishableKey = <YOUR_ENV_STRIPE_KEY>'
return {
loading: false,
lineItems: [
{
price: <YOUR_PRICE_ID>', // The id of the one-time price you created in your
Stripe dashboard
quantity: 1,
},
],
successURL: 'http://laravelbaseapi.test/vue/stripe/success',
cancelURL: 'http://laravelbaseapi.test/vue/stripe/cancel',
};
},
methods: {
submit () {
// You will be redirected to Stripe's secure checkout page
this.$refs.checkoutRef.redirectToCheckout();
},
},
};
</script>
Los códigos en negritas, son los que tienes que personalizar, que corresponde, al STRIPE_KEY, el identificador
del precio y las URLs para cuando es exitoso/cancelado el pago.
Creamos 3 rutas, dos de ejemplo para manejar el pago exitoso/cancelado que puedes personalizar y para el
componente anterior:
resources\js\vue\router.js
import OnePayment from "./componets/stripe/OnePayment.vue";
515
***
const routes = [
***
{
name: 'stripe',
path: '/vue/stripe/one-payment',
component: OnePayment
},
{
name: 'success',
path: '/vue/stripe/success',
component: List
},
{
name: 'cancel',
path: '/vue/stripe/cancel',
component: List
},
]
Al ejecutar, es probable que de un error como el siguiente:
v3:1 Uncaught (in promise) IntegrationError: The Checkout client-only integration is not
enabled. Enable it in the Dashboard at
https://dashboard.stripe.com/account/checkout/settings.
at Sl (v3:1:461068)
at e._handleMessage (v3:1:469393)
at e._handleMessage (v3:1:85275)
Desde el siguiente enlace:
https://dashboard.stripe.com/account/checkout/settings
Podemos habilitar el checkout de solo cliente en nuestra cuenta:
516
Figura 20-13: Habilitar integración solo en el cliente
El cúal es el motivo del error anterior.
De esta forma tan sencilla logramos implementar una plataforma de pago en nuestra aplicación, pero, el
problema que tenemos es que toda la operación queda en el cliente, es decir, no podemos procesar nada
en el servidor, para esto, debemos de crear una sesión, que viene siendo el equivalente de una orden a
Paypal, es decir, un objeto en el cual se encuentran datos de la compra como el identificador, estado, monto, etc.
Con esto, al darle click al boton de pay, deberia de aparecer la ventana de pago de Stripe en la cual, debes de
colocar alguna tarjeta de débito o crédito que puedes emplear del siguiente enlace:
https://docs.stripe.com/testing
Parámetros y sessionId definidos en el componente anterior
Desde el código anterior, empleamos el componente de stripe-checkout con las siguientes configuraciones:
● pk (string - requerido): Clave publicable de Stripe.
● sessionId (string - No requerido): El ID de la sesión de pago que se utiliza en la integración del cliente y el
servidor.
● lineItems (array[object] - No requerido): Un array de objetos que representan los artículos que el cliente
desea comprar, debemos de colocar el o los priceId configurados al producto que creamos en el
dashboard de Stripe.
● mode (string - No requerido): El modo de la sesión de pago, ya sea de pago o de suscripción.
● successUrl (string - No requerido): La URL a la que Stripe debe enviar a los clientes cuando se complete
el pago.
● cancelUrl (string - No requerido): La URL a la que Stripe debe enviar a los clientes cuando se cancela el
pago.
517
Sobre el sessionId
Es importante aclarar que al momento de emplear el sessionId, significa que el mismo fue generado en el
servidor y al generar el mismo:
$session = Checkout::guest()->create(
$priceId
,
[
'success_url' => $successRouteUrl,
'cancel_url' => $cancelUrl,
]
);
return $session->id;
En el código anterior de ejemplo, podemos ver que hay varios parámetros definidos:
<div>
<stripe-checkout
ref="checkoutRef"
mode="payment"
:pk="publishableKey"
:line-items="lineItems"
:success-url="successURL"
:cancel-url="cancelURL"
@loading="v => loading = v"
/>
<button @click="submit">Pay now!</button>
</div>
En el caso de que se definan también en el cliente mediante el plugin de Vue Stripe, los parámetros son
sobrescritos por los definidos al generar el sessionId en el servidor. Puedes generar el mismo sin emplear
Laravel Cashier mediante:
$stripe = new
\Stripe\StripeClient('sk_test_51QTMqcEHJX14M8EEL0ZbpBwsH5iAnHn6Am1HzExVgOMrgQpdpithk8z2o6iV
zaqRV6PQVNHw1oK4uKE8U0llui3800pP9vCanl');
$stripe->checkout->sessions->create([
'success_url' => 'https://example.com/success',
'line_items' => [
[
'price' => 'price_1MotwRLkdIwHu7ixYcPLm5uZ',
'quantity' => 2,
],
],
'mode' => 'payment',
518
]);
https://docs.stripe.com/api/checkout/sessions/create?lang=php
Veremos también más adelante cómo emplear Laravel Cashier (Stripe) para generar el sessionId.
Puntos importantes del código anterior
En donde dice <YOUR_PRICE_ID> debes de copiar el identificador del precio configurado en la figura 20-12.
Si colocas el ID de pago recurrente, veras un error como:
You specified 'payment' mode but passer a recurring price...
O si colocas algun ID invalido:
No such plan: ...
El método:
submit () {
this.$refs.checkoutRef.redirectToCheckout();
},
<stripe-checkout
ref="checkoutRef"
***
/>
<button @click="submit">Pay now!</button>
Es empleado en el componente para redireccionar a la página de cobro de Stripe en base a la configuración
establecida en los parámetros del componente.
Laravel Cashier (Stripe)
En este apartado, instalaremos el paquete oficial desarrollado por el equipo de Laravel para integrar Stripe en un
proyecto en Laravel, con el cual, podremos realizar toda clase de operaciones a la API de Stripe como manejo de
suscripciones, pagos, gestionar clientes, etc e integrarlos con otros componentes de Laravel como el usuario
(modelo User) como veremos más adelante.
Es importante señalar que el plugin que vimos anteriormente de Vue Stripe es independiente de esta solución,
aunque, ambas pueden emplearse en conjunto para diversas implementaciones como el manejo del sessionId
como se comentó al inicio de este apartado ya que, a la final ya sea por el plugin o por Laravel Cashier, en
ambos casos estamos empleando la plataforma de Stripe para la gestión de nuestra pasarela de pago.
Instalación y configuración
Comencemos instalando el paquete en nuestro proyecto mediante:
519
$ composer require laravel/cashier
Una vez instalado, el siguiente paso consiste en publicar los archivos de configuraciones, específicamente, las
migraciones:
$ php artisan vendor:publish --tag="cashier-migrations"
Ahora, ejecutamos las migraciones para crear; las tablas:
$ php artisan migrate
Cashier's migrations will add several columns to your users table. They will also create a new subscriptions table
to hold all of your customer's subscriptions and a subscription_items table for subscriptions with multiple prices.
Generamos el archivo de configuración:
$ php artisan vendor:publish --tag="cashier-config"
Y tendremos una salida como la siguiente que indica que se generó el archivo de config/cashier.php:
INFO Publishing [cashier-config] assets.
Copying file [vendor/laravel/cashier/config/cashier.php] to [config/cashier.php]
Ahora, debemos de indicar la entidad asociada a la API de Stripe para manejar los pagos, que usualmente es la
de usuario:
app\Models\User.php
use Laravel\Cashier\Billable;
class User extends Authenticatable
{
use HasApiTokens, HasFactory, Notifiable, Billable;
}
Y estos serían todos los pasos primordiales para instalar Stripe en un proyecto en Laravel.
API Key
A continuación, debes configurar las claves API de Stripe en el archivo .env de tu aplicación. Puedes obtener las
claves API de Stripe desde el panel de control de Stripe de la imagen 20-9 que establecemos en:
.env
STRIPE_KEY=your-stripe-key
STRIPE_SECRET=your-stripe-secret
520
STRIPE_WEBHOOK_SECRET=your-stripe-webhook-secret
Como comentamos, en el cliente, en nuestro caso Vue, se emplea la clave pública y en el servidor se emplea la
clave privada; dependiendo de la petición que quieras realizar a Stripe, puede que tengas que configurar la clave
de alguna forma, a continuación, se muestra cómo puedes configurar la clave de manera global a toda la clase:
\Stripe\Stripe::setApiKey(config('cashier')['secret']);
O en la solicitud:
$session = Checkout::guest()->create(
$priceId,
[
'mode' => 'payment',
'success_url' => $successRouteUrl,
'cancel_url' => $cancelUrl,
],
[
'api_key' => config('cashier')['secret'],
]
);
Es importante aclarar que si no publicas el archivo de configuración de cashier o no provees la clave al momento
de hacer una petición, veras un error que te indica que la clave no fue proveída:
[2024-12-16 13:54:48] production.ERROR: No API key provided. Set your API key when
constructing the StripeClient instance, or provide it on a per-request basis using the
`api_key` key in the $opts argument. {"userId":1,"exception":"[object]
(Stripe\\Exception\\AuthenticationException(code: 0): No API key provided. Set your API key
when constructing the StripeClient instance, or provide it on a per-request basis using the
`api_key` key in the $opts argument. at
/home/u410307842/***/vendor/stripe/stripe-php/lib/BaseStripeClient.php:333)
[stacktrace]
Si al ejecutar cualquiera de los métodos que veremos más adelante, vez un error como el siguiente:
Call to undefined function curl_version()
Significa que tienes que activar el curl en su sistema:
php.ini
extension=curl
521
Generar sessionID
Como comentamos anteriormente, el problema de emplear el plugin de emplear el plugin de Stripe Vue es que no
podemos confirmar el pago en el servidor si no suministramos el sessionId, imposibilidanto acciones
automáticas como registrar algún recurso electrónico al momento del pago por Stripe.
Para generar un sessionId empleando Laravel Cashier, tenemos:
app\Http\Controllers\Api\StripeController.php
<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Cashier\Checkout;
class StripeController extends Controller
{
// Orden/Session
function createSession(string $priceId, string $successURL =
'http://laravelbaseapi.test/vue/stripe/success', string $cancelUrl =
'http://laravelbaseapi.test/vue/stripe/cancel')
{
$session = Checkout::guest()->create($priceId, [
'mode' => 'payment',
'success_url' => $successURL,
'cancel_url' => $cancelUrl
]);
return $session->id;
}
}
Su ruta:
routes\api.php
// stripe
Route::get('stripe/create-session/{priceId}/{successURL?}/{cancelUrl?}',
[StripeController::class, 'createSession']);
Es importante aclarar que si retornas el objeto completo:
522
return $session;
En vez de su identificador, se redirecciona automáticamente a la página de pago de Stripe.
Quedando el componente en Vue como:
resources\js\vue\componets\stripe\OnePayment.vue
<template>
<div v-if="sessionID">
<!-- whitout sessionID -->
<!-- <StripeCheckout ref="checkoutRef" mode="payment" :pk="publishableKey"
:line-items="lineItems"
:successURL="successURL" :cancelURL="cancelURL"
@loading="v => loading = v" /> -->
<!-- with sessionID -->
<StripeCheckout :sessionId="sessionID" ref="checkoutRef" :pk="publishableKey"
@loading="v => loading = v" />
<button @click="submit">Pay</button>
</div>
</template>
<script>
import { StripeCheckout } from '@vue-stripe/vue-stripe';
export default {
components: {
StripeCheckout
},
async mounted() {
const res = await this.$axios.get('/api/stripe/create-session/' +
this.lineItems[0].price)
this.sessionID = res.data
},
data() {
***
return {
sessionID: '',
lineItems: [
***
],
***
}
},
523
***
}
</script>
Como puedes apreciar, el único cambio es una petición a la Rest Api mediante axios para generar el sessionId
que luego es empleado para establecerlo en el componente de Stripe de Vue, también, removemos parámetros
del componente de Stripe en Vue como el de las URLS, modo y los ítems de pago ya que los mismos son
generados al crear el sessionId.
Como comentamos anteriormente, la URL a emplear al momento de hacer el pago y redirecciona a la web es la
generada en el servidor, que, si utilizamos este componente de pago en diversas partes de la aplicación, puede
que quieras personalizar estos aspectos; desde el componente de Vue, podemos generar la URL en base a una
ruta con nombre:
const url = this.$router.resolve({
name: this.propYourRoute,
).fullPath
console.log(window.origin + url)
Pasar parametros, queries:
const url = this.$router.resolve({
name: this.propYourRoute,
params: {
slug: this.slug,
orden: this.sessionId,
},
query: query,
}).fullPath
console.log(window.origin + url)
Propagar parámetros:
const url = this.$router.resolve({
name: this.propYourRoute,
params: {
slug: this.slug,
orden: this.sessionId,
...this.extraParamsSuccessRoute
},
query: query,
}).fullPath
console.log(window.origin + url)
Y que luego puedes suministrar al servidor para que puedas personalizar las redirecciones.
524
Establecer el sessionID en el success_url
Podemos añadir la URL del sessionID a la URL de éxito, que luego podremos emplear para verificar el pago,
para ello, anexamos un parámetro via GET con {CHECKOUT_SESSION_ID}:
app\Http\Controllers\Api\StripeController.php
***
class StripeController extends Controller
{
// Orden/Session
function createSession(string $priceId, string $successURL =
'http://laravelbaseapi.test/vue/stripe/success', string $cancelUrl =
'http://laravelbaseapi.test/vue/stripe/cancel')
{
$session = Checkout::guest()->create($priceId, [
'mode' => 'payment',
'success_url' => $successURL.'?session_id={CHECKOUT_SESSION_ID}',
'cancel_url' => $cancelUrl
]);
return $session->id;
}
}
Desde el cliente, creamos un nuevo componente en Vue en donde ahora, al momento de tener un pago exitoso,
tendremos una URL como la siguiente:
http://laravelbaseapi.test/vue/stripe/success?session_id=cs_test_a1UYR***
Y mediante JavaScript, obtenemos el ID:
let params = new URLSearchParams(document.location.search)
params.get('session_id')
Quedando como:
resources\js\vue\componets\stripe\StripeSuccess.vue
<template>
<div class='container mx-auto'>
<h1>Order Success: {{ session_id }}</h1>
</div>
</template>
<script>
export default {
525
data() {
return {
session_id: ''
}
},
mounted() {
let params = new URLSearchParams(document.location.search)
this.session_id = params.get('session_id')
},
methods: {
}
}
</script>
Y registramos:
resources\js\vue\router.js
import StripeSuccess from "./componets/stripe/StripeSuccess.vue";
***
{
name: 'success',
path: '/vue/stripe/success',
component: StripeSuccess
},
Verificar sessionId en el servidor
Una vez realizado el pago, lo siguiente que debemos de hacer es verificar el mismo en el servidor, este paso es
opcional, pero recomendado para poder realizar procesamientos adicionales como una vez realizado el pago,
registrar los productos al cliente.
El método para verificar la orden en base al sessionID:
<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Checkout;
use Stripe\Checkout\Session;
class StripeController extends Controller
{
***
526
private function checkSessionById(string $sessionId): Session
{
return Cashier::stripe()->checkout->sessions->retrieve($sessionId);
}
}
Esto nos devuelve un objeto con TODA la información del pago como:
{
"id": "cs_test_a11YYufWQzNY63zpQ6QSNRQhkUpVph4WRmzW0zWJO2znZKdVujZ0N0S22u",
"object": "checkout.session",
"after_expiration": null,
"allow_promotion_codes": null,
"amount_subtotal": 2198,
"amount_total": 2198,
"automatic_tax": {
"enabled": false,
"liability": null,
"status": null
},
"billing_address_collection": null,
"cancel_url": null,
"client_reference_id": null,
"consent": null,
"consent_collection": null,
"created": 1679600215,
"currency": "usd",
"custom_fields": [],
"custom_text": {
"shipping_address": null,
"submit": null
},
"customer": null,
"customer_creation": "if_required",
"customer_details": null,
"customer_email": null,
"expires_at": 1679686615,
"invoice": null,
"invoice_creation": {
"enabled": false,
"invoice_data": {
"account_tax_ids": null,
"custom_fields": null,
"description": null,
"footer": null,
"issuer": null,
527
"metadata": {},
"rendering_options": null
}
},
"livemode": false,
"locale": null,
"metadata": {},
"mode": "payment",
"payment_intent": null,
"payment_link": null,
"payment_method_collection": "always",
"payment_method_options": {},
"payment_method_types": [
"card"
],
"payment_status": "unpaid",
"phone_number_collection": {
"enabled": false
},
"recovered_from": null,
"setup_intent": null,
"shipping_address_collection": null,
"shipping_cost": null,
"shipping_details": null,
"shipping_options": [],
"status": "open",
"submit_type": null,
"subscription": null,
"success_url": "https://example.com/success",
"total_details": {
"amount_discount": 0,
"amount_shipping": 0,
"amount_tax": 0
},
"url":
"https://checkout.stripe.com/c/pay/cs_test_a11YYufWQzNY63zpQ6QSNRQhkUpVph4WRmzW0zWJO2znZKdV
ujZ0N0S22u#fidkdWxOYHwnPyd1blpxYHZxWjA0SDdPUW5JbmFMck1wMmx9N2BLZjFEfGRUNWhqTmJ%2FM2F8bUA2SD
RySkFdUV81T1BSV0YxcWJcTUJcYW5rSzN3dzBLPUE0TzRKTTxzNFBjPWZEX1NKSkxpNTVjRjN8VHE0YicpJ2N3amhWY
HdzYHcnP3F3cGApJ2lkfGpwcVF8dWAnPyd2bGtiaWBabHFgaCcpJ2BrZGdpYFVpZGZgbWppYWB3dic%2FcXdwYHgl"
}
De aquí, podemos obtener información como el estatus, el precio, la moneda, la fecha, entre otros; en este libro,
no nos interesa enseñar a hacer una tienda en línea si no, solamente emplear la plataforma de Stripe para
realizar los pagos, así que, no vamos a registrar esta información de la base de datos, pero, es importante aclarar
que, antes de registrar los productos, debes de evaluar el estado que debe de estar como "paid":
528
$session->payment_status == 'paid'
Ya a partir de allí, puedes obtener los detalles como:
● El Estatus
● El identificador
● El precio
● La traza
Teniendo esto como clave, el código completo en base al criterio anterior:
app\Http\Controllers\Api\StripeController.php
<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use Laravel\Cashier\Cashier;
use Laravel\Cashier\Checkout;
use Stripe\Checkout\Session;
class StripeController extends Controller
{
***
private function checkSessionById(string $sessionId): Session
{
return Cashier::stripe()->checkout->sessions->retrieve($sessionId);
}
function checkPayment(string $sessionId)
{
$session = $this->checkSessionById($sessionId);
// dd($session->payment_intent);
// dd($session->payment_status);
if ($session->payment_status == 'paid') {
// To Do verificar que el session no existe en la BD
// To Do Registrar producto al cliente
// To Do register el pago BD
// $session->payment_status;
// $sessionId;
// $price = intdiv($session->amount_total, 100);
// return json_encode($session);
529
return response()->json('payment success');
}
// return json_encode($session);
return response()->json('payment not success', 400);
}
}
Es también importante aclarar que si vas a tomar el código anterior para completarlo para registrar un pago,
deberias de verificar al momento del estatus:
($session->payment_status == 'paid')
Si el pago ya fue procesado con anterioridad ANTES de registrar el producto al cliente, para evitar que un usuario
atacante, si descubre cómo funciona tu aplicación, mande el mismo sessionID (que a la final se puede obtener
desde el cliente) al servidor y en esencia, pueda registrar N productos por un solo pago.
Creamos la ruta:
routes\api.php
Route::get('stripe/get-session/{sessionId}', [StripeController::class, 'checkPayment']);
Enviar petición desde el cliente
Desde el cliente, en nuestro ejemplo desde la aplicación en Vue, debemos de enviar el sessionID una vez
realizado el pago para que sea procesado en el servidor, en nuestro ejemplo, mediante el controlador de
checkPayment() implementado anteriormente, para ello, dejamos las pautas que tienes que seguir:
resources\js\vue\componets\stripe\StripeSuccess.vue
<template>
<div class='container mx-auto'>
<h3>Order Success: {{ session_id }}</h3>
<hr>
<div v-if="statusPayment == 0">
<p>Processing...</p>
</div>
<div v-if="statusPayment == 1">
<p>Orden proccess successfully</p>
</div>
<div v-if="statusPayment == 2">
<p>One Error </p>
</div>
</div>
</template>
<script>
export default {
530
data() {
return {
session_id: '',
statusPayment: 0,
response: ''
}
},
mounted() {
this.processStripe()
},
methods: {
processStripe() {
// get session id from url
let params = new URLSearchParams(document.location.search)
this.session_id = params.get('session_id')
// console.log(params.get('session_id'))
// proccess stripe payment
axios.get('/api/stripe/get-session/' + this.session_id).then(response => {
this.statusPayment = 1
this.response = response.data
}).catch(error => {
this.statusPayment = 2
this.response = error
})
}
}
}
</script>
Como puedes apreciar en la implementación anterior, apenas se procese el pago que redirecciona al
componente anterior, tenemos una variable de control para manejar los estados del pago:
statusPayment: 0 // Pago en proceso
statusPayment: 1 // Pago procesado
statusPayment: 2 // Error en el pago
Cuyos valores configuramos en base a la respuesta del axios; la otra variable importante es la de response, con
la cual, obtenemos la respuesta del servidor para procesar o mostrar al cliente.
Payment Intent
El Payment Intent es un componente que forma parte de la session/orden de Stripe:
api/stripe/get-session/<SessionId>
531
"id": "cs_test_a1k86c4qyrKuOtbTOD34J8SNiSnhteakSgmVDzLsFLTOXmJZx6RLNlxIuW", // sessionID
***
"mode": "payment",
"payment_intent": "pi_3QclCMCWud7Ri9mJ02KPCCgM",
***
La documentación oficial la define como:
A PaymentIntent guides you through the process of collecting a payment from your customer. We recommend that
you create exactly one PaymentIntent for each order or customer session in your system. You can reference the
PaymentIntent later to see the history of payment attempts for a particular session.
A PaymentIntent transitions through multiple statuses throughout its lifetime as it interfaces with Stripe.js to
perform authentication flows and ultimately creates at most one successful charge.
Related guide: Payment Intents API
Por lo tanto, el Payment Intent viene formando parte del session de Stripe.
Puedes ver el session como la factura de cobro y el payment Intent como el comprobante del pago
Por ejemplo, si consultamos el session de Stripe apenas creemos el mismo mediante el controlador
createSession(), veremos que es nulo, ya que no ha habido ni siquiera un intento de pago.
"payment_intent": null,
Finalmente, para obtener el mismo desde Laravel Cashier:
app\Http\Controllers\Api\StripeController.php
<?php
***
use Stripe\PaymentIntent;
use Stripe\Stripe;
class StripeController extends Controller
{
***
function checkPaymentIntentByid(string $paymentIntentId)
{
Stripe::setApiKey(config('cashier.secret'));
return PaymentIntent::retrieve($paymentIntentId);
}
}
532
routes\api.php
Route::get('stripe/get-payment-intent/{paymentIntentId}', [StripeController::class,
'checkPaymentIntentByid'])
Como puedes apreciar en el código anterior, hay ciertos métodos que debes de proveer la clave secreta para
poder emplear dichos métodos:
Stripe::setApiKey(config('cashier.secret'));
Pagos rechazados
Los Payment Intent forman parte del session/orden de Stripe y puede haber varios o ninguno a lo largo del
periodo de vida de un Session, todo depende de lo que haga el cliente al momento de comprar el producto, por
ejemplo, apenas generamos el session y sin que el usuario haga alguna interacción con la página, el Payment
Intent es nulo, si hay un pago rechazado, en la web de las tarjetas de credito de testing de Stripe al final de la
página, hay un apartado de "Pagos rechazados" en el cual, si intentas hacer un pago con alguna de estas
tarjetas, el estatus del Payment Intent generado sería de "unregulated" lo cual puede ser interesante si te interesa
mantener un historial de los session/orden de Stripe géneros en tus sistemas, debes de conocer sus estatus.
Otros métodos de Laravel Cashier
En este apartado, vamos a conocer como realizar varias operaciones importantes con Laravel Cashier como la
creación de un customer o cliente en el API de Stripe y asociar al usuario, el uso del balance, entre otros.
Customers
Desde un usuario que recordemos hereda de la clase Billable, podemos realizar diversas operaciones como la
de crear un customer o cliente en Stripe:
$user->createAsStripeCustomer();
Si intentamos ejecutar el método anterior varias veces, veremos un error como el siguiente:
User is already a Stripe customer with ID cus_RXJGYoIOrdbidT.
Ya que el customer ya ha sido creado y es solamente uno por usuario; para obtener el customer:
$stripeCustomer = $user->asStripeCustomer();
Aunque si el customer no existe genera una excepción; también tenemos un método que obtiene el customer o lo
crea si no existe:
$stripeCustomer = $user->createOrGetStripeCustomer();
El código de prueba que implementamos queda como:
app\Http\Controllers\Api\StripeController.php
533
function stripeCustomer()
{
$user = User::find(3); // auth()->user()->id()
// $user->createAsStripeCustomer();
// $user->asStripeCustomer();
$user->createOrGetStripeCustomer();
}
routes\api.php
Route::get('stripe/customer', [StripeController::class, 'stripeCustomer']);
Balance
Con el balance, podemos asociar un saldo al usuario para que al momento de que el usuario vaya a realizar
alguna compra en la aplicación, se descuente primero de este saldo. Podemos preguntar por el balance:
$balance = $user->balance();
Agregar crédito:
$user->debitBalance(600,'Add balance');
O quitar crédito:
$user->creditBalance(500,'Remove balance');
El código de prueba que implementamos queda como:
app\Http\Controllers\Api\StripeController.php
function stripeBalance()
{
$user = User::find(3);
// $user->creditBalance(500,'Remove balance');
$user->debitBalance(600,'Add balance');
$balance = $user->balance();
dd($balance);
}
En los casos anteriores, indicamos un valor numérico multiplicado por 100 que corresponde al valor en una
moneda como dólares y un mensaje opcional que representa a la operación a realizar.
Los métodos para agregar o remover saldo requieren de un customer o cliente creado para poder emplearlos.
534
Métodos de pago e Intenciones de cobro
En este apartado, veremos cómo podemos agregar métodos de pago e intenciones de cobro al cliente, ambas
funcionalidades se realizan mediante un intent que son las intenciones para hacer "algo" que el cliente debe
procesar como lo es, realizar un cobro o configurar una tarjeta.
Desde la aplicación, también podemos crear intenciones de pago para que, luego el cliente autenticado lo
autorice desde la aplicación y se realice el cobro y de aquí el término de Intent o intención. El intent es
básicamente lo mismo que configuramos con el plugin de PayPal en la cual desde el cliente y mediante
JavaScript configuramos la orden que luego era procesada mediante el cliente con su usuario y contraseña y a
posterior en el servidor de la aplicación en Laravel.
Configurar tarjeta del cliente
Para realizar cobros a la cuenta del cliente, debe de configurar una tarjeta, para ello, configuramos una vista
como la siguiente:
resources\views\stripe\payment-method.blade.php
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="ie=edge">
<title>Document</title>
<script src="https://js.stripe.com/v3/"></script>
</head>
<body>
<input id="card-holder-name" type="text">
<div id="card-element"></div>
<button id="card-button">
Update Payment Method
</button>
<script>
const stripe = Stripe("{{ config('cashier.key')}}")
const elements = stripe.elements()
const cardElement = elements.create('card')
cardElement.mount("#card-element")
</script>
535
</body>
</html>
Con el HTML anterior, configuramos un campo de texto para colocar el nombre de la tarjeta:
<input id="card-holder-name" type="text">
Caja para la tarjeta:
<div id="card-element"></div>
Botón de acción:
<button id="card-button">
Update Payment Method
</button>
Con el script anterior, configuramos un elemento de Stripe para realizar operaciones como la de proveer una caja
para que el cliente provea una tarjeta.
La siguiente implementación es, obtener la carta:
cardElement
El nombre de la tarjeta:
const cardHolderName = document.getElementById('card-holder-name')
Y el intent generado en el servidor:
"{{ $intent->client_secret }}"
Y registrar la tarjeta en stripe al momento de que el usuario presione el botón:
resources\views\stripe\payment-method.blade.php
***
<script>
***
cardElement.mount("#card-element")
// process card
const cardHolderName = document.getElementById('card-holder-name')
const cardButton = document.getElementById('card-button')
cardButton.addEventListener('click', async (e) => {
536
const {
setupIntent,
error
} = await stripe.confirmCardSetup(
"{{ $intent->client_secret }}", {
payment_method: {
card: cardElement,
billing_details: {
name: cardHolderName.value
}
}
}
)
if (error) {
// error
console.error(error)
} else{
// success
console.log(setupIntent)
}
})
</script>
La ruta:
routes\web.php
route::get('/stripe/set-payment-method',function (){
// auth()->user()
$user = User::find(1);
return view('stripe.payment-method', ['intent'=> $user->createSetupIntent()]);
});
Obtener los métodos de pago
Una vez registrado un instrumento de pago, lo siguiente que podemos realizar es obtener la misma, para ello,
tenemos varios métodos, obtener todos los métodos de pago:
$user->paymentMethods()
Obtener el método por defecto:
$user->defaultPaymentMethod()
537
O por un identificador:
$user->findPaymentMethod("<ID>")
Los resultados son simplemente objetos, de los cuales como datos importantes puedes obtener el tipo, fecha,
nombre y poco más, ya que el número de la tarjeta por cuestiones de seguridad no es compartída por Stripe:
routes\web.php
route::get('/stripe/get-payment-method',function (){
// auth()->user()
$user = User::find(1);
dd($user->findPaymentMethod("<ID>"));
// dd($user->defaultPaymentMethod());
dd($user->paymentMethods());
});
Eliminar los métodos de pago
La siguiente operación que veremos, es la de eliminar los métodos de pago que configuramos anteriormente,
para ello, podemos eliminarlos todos:
$user = User::find(1);
$paymentMethod = $user->findPaymentMethod("<PAYMENT_ID>");
O por identificador:
$user = User::find(1);
$user->deletePaymentMethods();
routes\web.php
route::get('/stripe/delete-payment-method', function () {
$user = User::find(1);
$paymentMethod = $user->findPaymentMethod("<PAYMENT_ID>");
$paymentMethod->delete();
$user->deletePaymentMethods();
});
Crear intenciones de pago
En este apartado, veremos cómo podemos crear una intención de pago o payment intent, un payment intent no
es más que una factura que luego el cliente debe de autorizar al momento de la compra, así que, primero, se
crea el payment intent mediante:
routes\web.php
route::get('/stripe/create-payment-intent', function () {
538
$payment = $request->user()->pay(
$request->get('amount')
);
return $payment->client_secret;
});
Y lo que usamos desde el cliente, es el identificador de client_secret, en donde este cliente puede ser una
aplicación web, móvil, entre otras.
Procesar intenciones de pago
Una vez generado el payment intent como vimos anteriormente:
routes\web.php
route::get('/stripe/make-payment', function () {
$user = User::find(1);
$payment = $user->pay(100);
return view('stripe.payment-confirm', ['clientSecret' => $payment->client_secret]);
return $payment;
});
Es momento de procesarlo, para ello, es similar a lo que hicimos antes para registrar un instrumento de pago
desde el cliente, en donde, es necesario emplear un CardElement de Stripe:
resources\views\stripe\payment-confirm.blade.php
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="ie=edge">
<title>Document</title>
<script src="https://js.stripe.com/v3/"></script>
</head>
<body>
<input id="card-holder-name" type="text">
<div id="card-element"></div>
539
<button id="confirm-payment">
Confirm Payment
</button>
<script>
const stripe = Stripe("{{ config('cashier.key') }}")
const elements = stripe.elements()
const cardElement = elements.create('card')
cardElement.mount("#card-element")
document.querySelector('#confirm-payment').addEventListener('click', async () => {
const clientSecret = "{{ $clientSecret }}";
console.log(clientSecret)
const {
error,
paymentIntent
} = await stripe.confirmCardPayment(clientSecret, {
payment_method: {
card: cardElement,
}
});
if (error) {
console.error('Error al confirmar el pago:', error);
} else {
console.log('Pago confirmado:', paymentIntent);
}
});
</script>
</body>
</html>
Pero con la diferencia de que, se emplea la función de confirmCardPayment() la cual recibe como parámetro el
clientSecret generado en el servidor mediante el Payment Intent y la tarjeta; es importante notar que el retorno
de la función son argumentos por nombre, es decir, deben de tener exactamente el nombre mostrado en el
código anterior:
{
error,
paymentIntent
}
540
Suscripción
Vamos a conocer cómo podemos manejar las suscripciones en Stripe; las suscripciones no son más que un pago
recurrente cada día, semana, mes o una fecha personalizada; así que, lo primero que debes de hacer es crear un
precio recurrente según lo mostrado en la figura 20-12, recuerda que debes establecer un pago recurrente y NO
puntual.
A partir de aquí, empleamos el siguiente método:
routes/web.php
route::get('/stripe/new-subcription', function () {
$user = User::find(1);
dd(
$user->newSubscription(
'default',
'YOUR_RECURRENT_PRICE_ID'
)->create('<USER_PAYMENT_METHOD_ID>')
);
});
Mediante newSubscription() se registra una nueva suscripción a la entidad de tipo Billable configurado que en
nuestro ejemplo es el usuario, el método anterior, recibe 2 parámetros:
1. default, corresponde a una etiqueta que puede ser cualquiera, stripe explica: This subscription type is
only for internal application usage and is not meant to be shown to users. In addition, it should not contain
spaces and it should never be changed after creating the subscription.
2. El segundo argumento corresponde al precio recurrente creado anteriormente.
Desde Stripe puedes ver tus usuarios suscritos:
https://dashboard.stripe.com/test/subscriptions
Métodos importantes
Una vez suscrito el usuario, tenemos varios métodos importantes que podemos emplear:
Este método retorna un booleano que indica si está o no suscrito:
routes/web.php
route::get('/stripe/is-subcribed', function () {
$user = User::find(1);
dd($user->subscribed('default'));
});
541
Devuelve la suscripción del usuario en base a la etiqueta:
$user->subscription('default')
El "Período de Gracias" stripe lo define como: "March 5th that was originally scheduled to expire on March 10th,
the user is on their "grace period" until March 10th", es decir, una vez cancelada la suscripción, el usuario cuenta
con un período de gracias hasta que la suscripción finalice, aunque ya no cuente con una suscripción activa:
$user->subscription('default')->onGracePeriod()
Indica si la suscripción fue cancelada:
$user->subscription('default')->canceled()
Indica si la suscripción terminó:
$user->subscription('default')->ended()
Si la suscripción está en periodo de prueba, en este ejemplo, de 10 días:
$user->newSubscription('default', 'price_monthly')->trialDays(10)
Pregunta si la suscripción está en prueba o termino la prueba:
if ($user->onTrial('default')) {
// ...
}
if ($user->subscription('default')->onTrial()) {
// ...
}
if ($user->hasExpiredTrial('default')) {
// ...
}
if ($user->subscription('default')->hasExpiredTrial()) {
// ...
}
O ya es paga:
$user->subscription('default')->recurring()
Que el usuario cancele la suscripción:
$user->subscription('swimming')->cancel();
542
Si desea establecer una cantidad específica para el precio al crear la suscripción:
$user->newSubscription(
'default',
'YOUR_RECURRENT_PRICE_ID'
)->quantity(3)
->create('<USER_PAYMENT_METHOD_ID>')
En el ejemplo anterior, se cobraría por 3 el valor de la suscripción, al establecer una cantidad de 3, es decir, si el
precio era de un dólar por mes, se cobró 3 dólares por ese mes.
Desde la documentación oficial puedes encontrar muchas más información sobre las suscripciones y Stripe en
general, como el uso de cupones, facturas, agregar datos adicionales, periodos de prueba, etc.
Mediante el plugin de Vue Stripe
También podemos configurar suscripciones empleando el plugin de Vue Stripe, con esto, podemos EVITAR
registrar instrumentos de pago:
$user->newSubscription('default',
'YOUR_RECURRENT_PRICE_ID')->create('<USER_PAYMENT_METHOD_ID>')
Es decir, si es una aplicación pequeña y poco conocida, un usuario final será reacio de registrar sus instrumentos
crediticios en la aplicación, por desconfianza, además de que, para nosotros, los desarrolladores que solamente
queremos hacer una suscripción de algún servicio, es más complicado estar registrando los instrumentos de
pago para el único propósito de crear una suscripción; es importante mencionar que no es que los métodos
anteriores sean inviables, es que para la mayoría de las aplicaciones no es necesaria esa capa de integración
con la pasarela de pago.
Entonces, los cambios que debes de hacer para emplear el plugin de Vue Stripe para las suscripciones según la
implementación anterior, es, al generar el SessionID en el servidor, el modo es de tipo suscripción:
app\Http\Controllers\Api\StripeController.php
function createSession(string $priceId, string $successURL =
'http://laravelbaseapi.test/vue/stripe/success', string $cancelUrl =
'http://laravelbaseapi.test/vue/stripe/cancel')
{
$session = Checkout::guest()->create($priceId, [
// 'mode' => 'payment',
'mode' => 'subscription',
'success_url' => $successURL . '?session_id={CHECKOUT_SESSION_ID}',
'cancel_url' => $cancelUrl
]);
return $session->id;
}
543
Y en el plugin, recuerda establecer un precio de tipo recurrente:
lineItems: [ { price: <PRICE_ID_RECURRENT> }] // recurrent
Y eso es todo, ya con esto, puedes emplear el plugin no solo para hacer un pago único si no, para hacer una
suscripción.
Desde Laravel Cashier, también puedes configurar en vez de un pago único un pago recurrente, devolviendo el
objeto session:
function createSession(***)
{
$session = Checkout::guest()->create($priceId, [
// 'mode' => 'payment',
'mode' => 'subscription',
'success_url' => $successURL . '?session_id={CHECKOUT_SESSION_ID}',
'cancel_url' => $cancelUrl
]);
return $session; // retorna la pagina de https://checkout.stripe.com/c/***
// return $session->id;
}
Configuración de la moneda
Puedes variar la moneda, por ejemplo, el euro:
.env
CASHIER_CURRENCY=eur
Configurar clave pública de manera global
Recuerda definir el acceso a la clave pública de Stripe de manera global para que pueda ser consumida
fácilmente desde Vue y que, cuando pases a producción, solamente tengas que prescindir del valor de:
.env
STRIPE_KEY=***
STRIPE_SECRET=***
Y acceder al archivo de configuración:
config\cashier.php
'key' => env('STRIPE_KEY', <YOURKEY>),
544
'secret' => env('STRIPE_SECRET', <YOURSECRET>),
Quedando como:
resources\views\vue.blade.php
@if (Auth::check())
<script>
window.Laravel = {!! json_encode([
'isLoggedIn' => true,
'user' => Auth::user(),
'token' => session('token'),
'clientStripe' => config('cashier.key'),
]) !!}
</script>
@else
<script>
window.Laravel = {!! json_encode([
'isLoggedIn' => false,
'clientStripe' => config('cashier.key'),
]) !!}
</script>
mobiledetectlib
Al momento de desarrollar aplicaciones web, es indispensable pensar en la adaptabilidad de cualquier aplicación
web y uno de estos aspectos pasa por detectar de manera programática el tipo de dispositivo con el cual se está
accediendo a la aplicación: ¿es un dispositivo móvil o un ordenador de escritorio?.
Mediante la detección del dispositivo, podemos hacer operaciones como mejorar el desempeño, usualmente
recursos como CSS y JS o banners u otros elementos de interfaz gráfica, queremos variar si es un dispositivo
móvil o de escritorio, también podemos mejorar la carga de recursos, en los dispositivos móviles es crucial ya
que, se trata de un dispositivo más limitado que de escritorio y que también es un aspecto importante para el
SEO, que la página se cargue lo más rápido posible y es precisamente optimizando los recursos.
Compatibilidad con Tecnologías Emergentes
Este paquete, permite determinar entre una gran cantidad de dispositivos como:
● isiPhone()
● isXiaomi()
● isAndroidOS()
● isiOS()
● isiPadOS()
545
Aunque, usualmente lo que queremos es determinar si un usuario está navegando por nuestra aplicación en
Laravel mediante un teléfono o computador, para ello, tenemos acceso a la siguiente función:
$detect = new MobileDetect();
$detect->isMobile();
Este es un paquete para PHP y no específico para Laravel, aunque Laravel tiene unos pocos paquetes para
lograr este objetivo:
● https://github.com/riverskies/laravel-mobile-detect
● https://github.com/jenssegers/agent
Al momento en el cual se escriben estas palabras, llevan varios años sin actualizar y no funcionan es las últimas
versiones de Laravel, asun así, este paquete lo podemos emplear in mayor problema; desde nuestro proyecto en
Laravel, ejecutamos el siguiente comando de composer:
$ composer require mobiledetect/mobiledetectlib
Para emplearlo, podemos crear una función de ayuda, como vimos en el apartado de los helpers (revisa dicho
apartado para que lo configures si quieres probar el paquete y poder emplearlo desde cualquier lugar de la
aplicación, como controladores y vistas):
app\Helpers\helpers.php
<?php
use Detection\MobileDetect;
function isMobile()
{
$detect = new MobileDetect();
// var_dump($detect->getUserAgent());
try {
return $detect->isMobile();
} catch (\Detection\Exception\MobileDetectException $th) {
}
return false;
}
Y con esto, podremos acceder a la función isMobile() desde cualquier parte de nuestra aplicación, ya sea un
archivo blade, controlador u otro.
El control de la excepción se emplea por si el paquete no es capaz de detectar el dispositivo, modo u otro.
Laravel Fortify
Laravel Fortify es un paquete para la autenticación, registro, recuperación de contraseña, verificación de correo
electrónico y más, en pocas palabras, permite realizar las mismas funcionalidades que Laravel Breeze que
546
empleamos antes, pero la diferencia radica en que no es tan intrusivo, cuando instalamos laravel Breeze el
mismo instala Tailwind.css y género varios componentes, controladores, vistas y rutas asociadas; en el caso de
Laravel Fortify no es así y nos provee las mismas características pero sin necesidad de la interfaz gráfica, por lo
tanto, es particularmente útil cuidando quieres desarrollar un backend de autenticación más personalizado que el
que nos ofrece Breeze; es importante señalar a que si estás empleando Laravel Breeze o alguna solución similar
no es necesario utilizar Laravel Fortify.
Otra posible comparación que pueda que estés realizando es con Sanctum, Laravel Fortify y Laravel Sanctum no
son paquetes mutuamente excluyentes ni competidores si no, se pueden emplear en un mismo proyecto en caso
de que se lo requiere, Laravel Sanctum solo se ocupa de administrar tokens API y autenticar a los usuarios
existentes mediante cookies o tokens de sesión. Sanctum no proporciona ninguna ruta que maneje el registro de
usuarios, el restablecimiento de contraseñas, etc, en pocas palabras Sanctum está enfocado en la autenticación
de una Api Rest y Laravel Fortify para una aplicación web tradicional.
Instalación y configuración
Para instalar Laravel Fortify empleamos el siguiente comando:
$ composer require laravel/fortify
El siguiente paso es ejecutar el comando de instalación:
$ php artisan fortify:install
Este comando generará las migraciones, archivo de configuración, providers entre otros.
Ejecutamos las migraciones:
$ php artisan migrate
Y con esto, ya podemos emplear Laravel Fortify.
Características
Laravel Fortify cuenta con varias características que podemos habilitar o deshabilitar a gusto:
config\fortify.php
'features' => [
Features::registration(),
Features::resetPasswords(),
Features::emailVerification(),
],
Las mismas permite des/habilitar la opción de registro, reiniciar la contraseña y verificación por emails
respectivamente, en caso de que no quieras emplear alguna o varias de estas opciones, simplemente la debes
de comentar, por ejemplo, si quieres desactivar la verificación por email:
547
'features' => [
Features::registration(),
Features::resetPasswords(),
// Features::emailVerification(),
],
Para ejemplificar su uso, si hacemos un:
$ php artisan r:l
Veremos todas las rutas generadas por Fortify:
GET|HEAD register ............................................ register › Laravel\Fortify
› RegisteredUserController@create
POST register ........................................................
Laravel\Fortify › RegisteredUserController@store
POST reset-password ................................... password.update ›
Laravel\Fortify › NewPasswordController@store
GET|HEAD reset-password/{token} ........................... password.reset ›
Laravel\Fortify › NewPasswordController@create
GET|HEAD two-factor-challenge ......... two-factor.login › Laravel\Fortify ›
TwoFactorAuthenticatedSessionController@create
POST two-factor-challenge ............................. Laravel\Fortify ›
TwoFactorAuthenticatedSessionController@store
GET|HEAD up
...........................................................................................
....................
GET|HEAD user/confirm-password ....................................... Laravel\Fortify ›
ConfirmablePasswordController@show
POST user/confirm-password ................... password.confirm › Laravel\Fortify ›
ConfirmablePasswordController@store
GET|HEAD user/confirmed-password-status .. password.confirmation › Laravel\Fortify ›
ConfirmedPasswordStatusController@show
POST user/confirmed-two-factor-authentication two-factor.confirm › Laravel\Fortify ›
ConfirmedTwoFactorAuthenticationC…
PUT user/password ................................. user-password.update ›
Laravel\Fortify › PasswordController@update
PUT user/profile-information . user-profile-information.update › Laravel\Fortify ›
ProfileInformationController@update
POST user/two-factor-authentication ..... two-factor.enable › Laravel\Fortify ›
TwoFactorAuthenticationController@store
DELETE user/two-factor-authentication .. two-factor.disable › Laravel\Fortify ›
TwoFactorAuthenticationController@destroy
GET|HEAD user/two-factor-qr-code .................... two-factor.qr-code ›
Laravel\Fortify › TwoFactorQrCodeController@show
548
GET|HEAD user/two-factor-recovery-codes ........ two-factor.recovery-codes ›
Laravel\Fortify › RecoveryCodeController@index
POST user/two-factor-recovery-codes ....................................
Laravel\Fortify › RecoveryCodeController@store
GET|HEAD user/two-factor-secret-key ........... two-factor.secret-key › Laravel\Fortify
› TwoFactorSecretKeyController@show
Si ingresamos a la de login, veremos un error como el siguiente:
http://larafirstepspackages.test/login
Target [Laravel\Fortify\Contracts\LoginViewResponse] is not instantiable.
Ya que, como comentamos antes, no contamos con páginas o pantallas listas como en Breeze sí no, debemos de
crearlas de manera manual (o usarlas con otras tecnologías como consumir estas rutas mediante una app en
Vue).
Si comentas el módulo de registrar:
config\fortify.php
// Features::registration(),
E intentas ir a la ruta:
http://larafirstepspackages.test/register
Verás que devuelve una página de 404 ya que, acabamos de deshabilitar la acción para registrar usuarios.
Si exploras el objeto:
Fortify
Veremos muchas opciones que podemos personalizar de Fortify; si revisamos el archivo de:
app\Providers\FortifyServiceProvider.php
Veremos todas las acciones de Fortify que podemos personalizar, como el tiempo de bloqueo tras logins fallidos:
RateLimiter::for('login', function (Request $request) {
***
Por ejemplo, podemos especificar la vista para el login:
class FortifyServiceProvider extends ServiceProvider
{
/**
549
* Register any application services.
*/
public function register(): void
{
//
}
public function boot(): void
{
***
Fortify::loginView(function(){
return view('auth.login');
});
}
}
O para registrarse:
class FortifyServiceProvider extends ServiceProvider
{
/**
* Register any application services.
*/
public function register(): void
{
//
}
public function boot(): void
{
***
Fortify::registerView(function(){
return view('auth.register');
});
}
}
Creemos algunas vistas muy sencillas como las siguientes:
resources\views\auth\register.blade.php
<!DOCTYPE html>
<html lang="en">
<head>
550
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="ie=edge">
<title>Document</title>
</head>
<body>
@if ($errors->any())
@foreach ($errors->all() as $e)
<div>
{{ $e }}
</div>
@endforeach
@endif
<form action="" method="post">
@csrf
<input type="text" name="name" placeholder="name">
<input type="email" name="email">
<input type="password" name="password" >
<input type="submit" value="Send">
</form>
</body>
</html>
resources\views\auth\login.blade.php
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="ie=edge">
<title>Document</title>
</head>
<body>
@if ($errors->any())
@foreach ($errors->all() as $e)
<div>
{{ $e }}
</div>
@endforeach
@endif
<form action="" method="post">
551
@csrf
<input type="text" name="username">
<input type="password" name="password">
<input type="submit" value="Send">
</form>
</body>
</html>
Y al ingresar a las rutas correspondientes:
http://larafirstepspackages.test/login
http://larafirstepspackages.test/register
Verás el sistema completo para el login y registrarse provisto por Fortify; estas son solamente algunas acciones
que tenemos disponibles, explorar su su uso mediante los ejemplos anteriores para que se entienda de manera
práctica el funcionamiento del paquete; ya queda por parte del lector explorar el resto de las funcionalidades para
que puedas emplearlos en tus proyectos que requieran emplear Fortify para crear un sistema de autenticación
completamente personalizado en vez de Breeze.
Documentación oficial:
https://laravel.com/docs/master/fortify
Verificación de usuario por emails
En este apartado, veremos cómo podemos activar la verificación por emails de los usuarios, estos pasos los
puedes seguir si estás empleando Laravel Fortify o Laravel Breeze, si revisamos la migración o modelo de
usuarios, veremos la siguiente columna:
email_verified_at
La cual,especifica si un usuario está verificado o no, es decir:
1. Nulo significa que el usuario no está verificado
2. Una fecha y hora, indica la fecha y hora en la en la cual el usuario fue verificado
La verificación de usuarios es un paso importante en la mayoría de las aplicaciones hoy en día, ya que, mediante
esta verificación, podemos garantizar que el email empleado al momento de registrar el usuario es válido; para
realizar la verificación consiste en que se envía un correo con un token especial que es generado a partir del
usuario que se desea verificar, este token llega por defecto al email del usuario que se desea verificar, por lo
tanto, si este email no es válido, significa que el correo no llegará imposibilitando la verificación del usuario.
La verificación del usuario es un paso importante y que es fácilmente manejable desde la aplicación para
bloquear ciertas acciones como compras en la aplicación sí el usuario no ha sido verificado.
Para esto, debemos de habilitar la característica desde el siguiente archivo:
552
config\fortify.php
Features::emailVerification(),
Luego, debemos de habilitar la verificación desde el modelo:
app\Models\User.php
use Illuminate\Contracts\Auth\MustVerifyEmail;
***
class User extends Authenticatable implements MustVerifyEmail {}
Luego, si estás empleando Laravel Breeze, cada vez que registres a un usuario, llegará un correo de verificación
como el siguiente:
Figura 20-7: Correo de registro
A nivel de tu aplicación, puedes preguntar si el usuario está o no autenticado mediante:
$user->email_verified_at == null
Breeze
En este apartado, trataremos otros aspectos importantes sobre la personalización de Laravel Breeze que
instalamos anteriormente para tener un sistema para la autenticación en nuestra aplicación.
553
Personalizar vistas de autenticación en Breeze
Podemos personalizar fácilmente el contenido de varias vistas para realizar diversas operaciones como el login,
reset password entre otros en la carpeta de:
resources\views\auth
Laravel Sociality
Con este paquete, podemos agregar autenticación social en Laravel mediante Gmail, Facebook (pendiente). xxx
Extra: CKEditor
Otro paquete que quiero presentar es el del CKEditor, como uno de los plugins más famosos para el WYSIWYG,
en otras palabras, para crear contenido enriquecido, es una especie de word, pero para la web, así de simple,
plugins de este tipo hay muchos pero, me atrevo a decir que hay pocos que tengan tanto tiempo en el mercado,
calidad y desarrollo tan vivo y en constante evolución, con plugins para vanilla JavaScript, Vue, React entre otros,
con muchísimas opciones de personalización y con módulos para personalizar el contenido como:
1. Tipo de textos entre párrafos y títulos
2. Lista, tablas imágenes
3. Formatos de texto como tachaduras, color tamaño, fuente o itálicas
4. Código
5. Embeber HTML personalizado
6. Carga de imágenes
Desde el plugin, podemos personalizar de manera total qué características queramos importar módulos como los
anteriores, pero hay muchísimos que podemos emplear y que puedes ver en la documentación oficial:
https://ckeditor.com/docs/ckeditor5/latest/features/
Aunque todo esto nos puede traer como desventaja que su configuración a veces no es tan trivial y esto es
debido al gran nivel de personalización que tiene disponible, además de los constantes cambios en
implementación en donde en una versión importamos un paquete de la siguiente manera:
import { ClassicEditor } from 'ckeditor5';
Pero, en recientes para una misma versión, ahora se instala de la siguiente manera:
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';
Que es la más reciente, el problema principal es que, en Internet encontrarás muchas implementaciones que no
son válidas para las versiones más recientes. La documentación oficial en muchas veces puede ser abstracta,
554
confusa y poca intuitiva y cuesta mucho entender cómo podemos implementar opciones sencillas como poder
editar el HTML, hacer un upload, etc; así que, en resumidas palabras, es un plugin excelente con una curva de
aprendizaje algo elevada.
Cómo comentamos antes, CKEditor es un plugin con muchas actualizaciones mayores que al momento en el
cual se escriben estas palabras va por la versión 43 y como puedes deducir, lanzan varias versiones mayores en
un solo año, lo que puede traer como consecuencia que parte de las implementaciones mostradas en esta guía
puedan requerir variantes.
Laravel y CKEditor
En este punto, seguramente te estás preguntando porque estamos tratando un plugin que es para JavaScript y
no específico para Laravel, la razón de esto se debe a que, al crear procesos administrativos, este tipo de
complementos viene siendo prácticamente obligatorio, y al ser tan flexibles, lo podemos también instalar en
Laravel.
Pasos para configurar Ckeditor con Laravel, lo primero que tenemos que hacer es ir a la siguiente página:
https://ckeditor.com/ckeditor-5/builder/
Puedes seleccionar una opción como la de "Collaborative Article Editor" Y seguir el formulario paso por paso;
este plugin tiene una excelente herramienta para construir el editor de una manera muy fácil, simplemente
seleccionando componentes:
555
Figura 20-8: Seleccionar características de CKEditor.
Hay opciones muy interesantes, pero, no todas son gratis, las que tengan una estrella, no las puedes seleccionar
a menos que quieras comprarlas.
Del bundle, solamente vamos a necesitar el archivo main.js, que lo renombramos como ckeditor.js y el CSS que
puedes encontrar en el archivo de index.html:
<link rel="stylesheet" href="https://cdn.ckeditor.com/ckeditor5/43.0.0/ckeditor5.css">
Click derecho sobre el navegador y guarda el CSS en un archivo llamado ckeditor.css.
556
Figura 20-9: Descargar el build
Copiamos los archivos anteriores:
resources\js\ckeditor.js
resources\css\ckeditor.css
Instalamos CKEditor, que es la dependencia del build anterior:
$ npm i ckeditor5
Una vez copiado el CSS y JS de la solución anterior, debemos de referenciarlas en el archivo Vite para que forme
parte el bundle al ejecutar el comando de:
$ npm run build
$ npm run dev
Agreganos:
vite.config.js
export default defineConfig({
plugins: [
557
vue(),
laravel({
input: [
***
'resources/css/ckeditor.css',
'resources/js/ckeditor.js'
],
refresh: true,
}),
],
});
El archivo JS empleado en este libro luce similar a este:
resources\js\ckeditor.js
import {
ClassicEditor,
Autoformat,
AutoImage,
AutoLink,
Autosave,
BalloonToolbar,
BlockQuote,
Bold,
Bookmark,
CloudServices,
Code,
CodeBlock,
Essentials,
FindAndReplace,
FontBackgroundColor,
FontColor,
FontFamily,
FontSize,
Heading,
Highlight,
HorizontalLine,
HtmlEmbed,
ImageBlock,
ImageCaption,
ImageInline,
ImageInsertViaUrl,
ImageResize,
ImageStyle,
ImageTextAlternative,
558
ImageToolbar,
ImageUpload,
Indent,
IndentBlock,
Italic,
Link,
LinkImage,
List,
ListProperties,
MediaEmbed,
Paragraph,
PasteFromOffice,
SourceEditing,
SpecialCharacters,
SpecialCharactersArrows,
SpecialCharactersCurrency,
SpecialCharactersEssentials,
SpecialCharactersLatin,
SpecialCharactersMathematical,
SpecialCharactersText,
Strikethrough,
Table,
TableCellProperties,
TableProperties,
TableToolbar,
TextTransformation,
TodoList,
Underline
} from 'ckeditor5';
import 'ckeditor5/ckeditor5.css';
// // import './style.css';
/**
* Create a free account with a trial: https://portal.ckeditor.com/checkout?plan=free
*/
const LICENSE_KEY = 'GPL'; // or <YOUR_LICENSE_KEY>.
const editorConfig = {
toolbar: {
items: [
'sourceEditing',
'|',
'heading',
'|',
559
'fontSize',
'fontFamily',
'fontColor',
'fontBackgroundColor',
'|',
'bold',
'italic',
'underline',
'|',
'link',
'insertTable',
'highlight',
'blockQuote',
'codeBlock',
'|',
'bulletedList',
'numberedList',
'todoList',
'outdent',
'indent'
],
shouldNotGroupWhenFull: false
},
plugins: [
Autoformat,
AutoImage,
AutoLink,
Autosave,
BalloonToolbar,
BlockQuote,
Bold,
Bookmark,
CloudServices,
Code,
CodeBlock,
Essentials,
FindAndReplace,
FontBackgroundColor,
FontColor,
FontFamily,
FontSize,
Heading,
Highlight,
HorizontalLine,
HtmlEmbed,
ImageBlock,
560
ImageCaption,
ImageInline,
ImageInsertViaUrl,
ImageResize,
ImageStyle,
ImageTextAlternative,
ImageToolbar,
ImageUpload,
Indent,
IndentBlock,
Italic,
Link,
LinkImage,
List,
ListProperties,
MediaEmbed,
Paragraph,
PasteFromOffice,
SourceEditing,
SpecialCharacters,
SpecialCharactersArrows,
SpecialCharactersCurrency,
SpecialCharactersEssentials,
SpecialCharactersLatin,
SpecialCharactersMathematical,
SpecialCharactersText,
Strikethrough,
Table,
TableCellProperties,
TableProperties,
TableToolbar,
TextTransformation,
TodoList,
Underline
],
balloonToolbar: ['bold', 'italic', '|', 'link', '|', 'bulletedList', 'numberedList'],
fontFamily: {
supportAllValues: true
},
fontSize: {
options: [10, 12, 14, 'default', 18, 20, 22],
supportAllValues: true
},
heading: {
options: [
{
561
model: 'paragraph',
title: 'Paragraph',
class: 'ck-heading_paragraph'
},
{
model: 'heading1',
view: 'h1',
title: 'Heading 1',
class: 'ck-heading_heading1'
},
{
model: 'heading2',
view: 'h2',
title: 'Heading 2',
class: 'ck-heading_heading2'
},
{
model: 'heading3',
view: 'h3',
title: 'Heading 3',
class: 'ck-heading_heading3'
},
{
model: 'heading4',
view: 'h4',
title: 'Heading 4',
class: 'ck-heading_heading4'
},
{
model: 'heading5',
view: 'h5',
title: 'Heading 5',
class: 'ck-heading_heading5'
},
{
model: 'heading6',
view: 'h6',
title: 'Heading 6',
class: 'ck-heading_heading6'
}
]
},
image: {
toolbar: [
'toggleImageCaption',
'imageTextAlternative',
562
'|',
'imageStyle:inline',
'imageStyle:wrapText',
'imageStyle:breakText',
'|',
'resizeImage'
]
},
initialData:
'...',
licenseKey: LICENSE_KEY,
link: {
addTargetToExternalLinks: true,
defaultProtocol: 'https://',
decorators: {
toggleDownloadable: {
mode: 'manual',
label: 'Downloadable',
attributes: {
download: 'file'
}
}
}
},
list: {
properties: {
styles: true,
startIndex: true,
reversed: true
}
},
menuBar: {
isVisible: true
},
placeholder: 'Type or paste your content here!',
table: {
contentToolbar: ['tableColumn', 'tableRow', 'mergeTableCells',
'tableProperties', 'tableCellProperties']
}
};
if(document.querySelector('#editor')){
ClassicEditor.create(document.querySelector('#editor'), editorConfig);
}
563
Y el CSS que puedes personalizar a gusto, en este archivo, puedes remover el CSS que no vamos a usar y que
sea necesario para que el HTML mostrado en el CKEditor se vea correctamente, según pruebas realizadas en el
libro, con el siguiente CSS es suficiente:
resources\css\ckeditor.css
.ck-content ol,
.ck-content ul {
padding: 15px;
}
Puedes encontrar el código completo en el repositorio del libro. Ejecutamos:
$ npm run build
Configuramos en el formulario para crear el post, para que, el contenido de la publicación sea configurado con
CKEditor y poder definir contenido enriquecido:
resources\views\dashboard\post\_form.blade.php
<div id="editor">
</div>
***
@vite(['resources/css/ckeditor.css', 'resources/js/ckeditor.js'])
Embeber CKEditor dentro del formulario
En este apartado, veremos cómo podemos utilizar o embeber CKEditor como parte del formulario de
publicaciones, para ello, comencemos ocultando el textarea de contenido que emplearemos para volcar el
contenido del CKEditor:
<textarea class='form-control !hidden content' name="content">{{ old('content',
$post->content) }}</textarea>
<div id="editor">
{!! old('content', $post->content) !!}
</div>
El:
!hidden
Es para forzar el ocultado, ya que, la clase de form-control usa la regla de display; también puedes remover la
clase de form-control en su lugar.
Para que aparezca el contenido HTML de la publicación, debes de remover la opción de:
564
resources\js\ckeditor.js
// initialData: '<h2>Congrat***',
La implementación que vamos a realizar es, colocar un listener sobre el evento submit del formulario con
JavaScript, volcar el contenido del CKEditor en el textarea oculto; para ello, definimos un identificador en los
formularios:
resources\views\dashboard\post\create.blade.php
resources\views\dashboard\post\edit.blade.php
<form id="myForm" ***
Guardamos una referencia del editor, para ello, se resuelve como una promesa que al estar ya creado el
CKEditor, lo guardamos en el objeto window para que tenga alcance global de toda la aplicación:
resources\js\ckeditor.js
***
if (document.querySelector('#editor')) {
ClassicEditor.create(document.querySelector('#editor'), editorConfig)
.then(editor => window.editor = editor)
}
Y finalmente creamos el listener y volcamos el contenido del editor mediante la función de getData() que
devuelve el contenido HTML en el textarea oculto para el contenido:
resources\views\dashboard\post\_form.blade.php
***
<script>
document.querySelector('#myForm').addEventListener('submit', function(e) {
document.querySelector('.content').value = editor.getData()
})
</script>
Proceso de Upload
De momento, tenemos un excelente plugin con el cual podemos crear contenido enriquecido, poder formatear el
texto, crear tablas entre otros, pero, falta una funcionalidad muy importante como lo es, la carga de imágenes, si
arrastramos una imagen al CKEditor, veremos un mensaje como el siguiente:
ckeditor5.js?v=efbb40b8:9479 filerepository-no-upload-adapter
Read more:
https://ckeditor.com/docs/ckeditor5/latest/support/error-codes.html#error-filerepository-no
-upload-adapter
565
La página oficial es:
https://ckeditor.com/docs/ckeditor5/latest/features/images/image-upload/image-upload.html
Para ello tenemos varias maneras; una de ellas es emplear los plugins de CKBox y CKFinder que son premium,
es decir, debe de pagar por ellas, o empleando un par de maneras que son las de un Simple Upload Adapter o un
adaptador personalizado (el adaptador es el término que emplea CKEditor para cargar archivos) que veremos a
continuación.
Antes de pasar a las implementaciones para el upload, puedes realizar las siguientes configuraciones opcionales
y permiten una vez cargada la imagen, poder ajustar el formato, mediante el siguiente toolbar:
toolbar: [
'toggleImageCaption',
'imageTextAlternative',
'|',
'imageStyle:inline',
'imageStyle:wrapText',
'imageStyle:breakText',
'|',
'resizeImage'
]
Que viene de:
import {
***
ImageBlock,
ImageCaption,
ImageInline,
ImageInsert,
ImageInsertViaUrl,
ImageResize,
ImageStyle,
ImageTextAlternative,
ImageToolbar,
Image
} from 'ckeditor5';
***
plugins: [
***
ImageBlock,
ImageCaption,
ImageInline,
ImageInsert,
ImageInsertViaUrl,
566
ImageResize,
ImageStyle,
ImageTextAlternative,
ImageToolbar,
]
image: {
toolbar: [
'toggleImageCaption',
'imageTextAlternative',
'|',
'imageStyle:inline',
'imageStyle:wrapText',
'imageStyle:breakText',
'|',
'resizeImage'
]
},
Simple Upload Adapter
Cómo especifica su nombre, esta es la solución más simple para el upload.
Para emplear el adaptador simple, debemos de importar el plugin de SimpleUploadAdapter e implementar la
opción de simpleUpload, específicamente la opción uploadUrl especificando la URL del controlador quedando
el adaptador como:
import {
ClassicEditor,
SimpleUploadAdapter,
***
} from 'ckeditor5';
***
const editorConfig = {
***
plugins: [
SimpleUploadAdapter,
***
],
simpleUpload:{
uploadUrl : '/dashboard/post/upload/ckeditor',
headers: {
'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
}
},
***
567
};
Adicionalmente, configuramos el token CSRF ya que, al igual que ocurre en los controladores de gestión, es
necesario proveer el token para evitar el error de tipo 419; el token que obtenemos se encuentra establecido en
una etiqueta meta en el layout:
resources\views\dashboard\master.blade.php
<meta name="csrf-token" content="{{ csrf_token() }}">
Finalmente, arrastra una imagen al CKEditor, utiliza la consola de desarrolladores, específicamente la pestaña de
Network para poder ver problemas en el servidor.
Desde el controlador especificado, lo definimos de la siguiente manera, lo importante es notar que, en base a la
documentación oficial:
https://ckeditor.com/docs/ckeditor5/latest/features/images/image-upload/simple-upload-adapter.html
Respuesta exitosa:
{
"urls": {
"default": "https://example.com/images/foo.jpg",
"800": "https://example.com/images/foo-800.jpg",
"1024": "https://example.com/images/foo-1024.jpg",
"1920": "https://example.com/images/foo-1920.jpg"
}
}
O:
{
"url": "https://example.com/images/foo.jpg"
}
Errores en la carga:
{
"error": {
"message": "The image upload failed because the image was too big (max 1.5MB)."
}
}
Teniendo claro cuál es la respuesta de éxito y error, implementamos el siguiente controlador:
app\Http\Controllers\Dashboard\PostController.php
568
use Illuminate\Http\Request;
***
public function uploadCKEditor(Request $request)
{
if (!auth()->user()->hasPermissionTo('editor.post.update')) {
return response()->json(['error' => 'No tienes permiso, PAGAME'], 500);
}
$validator = validator()->make($request->all(), [
'upload' => 'required|mimes:jpeg,jpg,png|max:10240'
]);
if ($validator->fails()) {
return response()->json(['error' => $validator->errors()->first()], 500);
}
// image
// $filename = $request->upload->getClientOriginalName();
$filename = time() . '.' . $request->upload->extension();
$request->upload->move(public_path('uploads/posts'), $filename);
return response()->json(['url' => '/uploads/posts/' . $filename]);
// return response()->json(['url' => 'uploads/posts' . $filename]);
// image
}
Su ruta:
routes\web.php
Route::post('post/upload/ckeditor', [App\Http\Controllers\Dashboard\PostController::class,
'uploadCKEditor'])->name('post.upload.ckeditor');
Si seleccionas la imagen, verás un toolbar con algunas opciones que puedes personalizar mediante:
const editorConfig = {
toolbar: {
***
},
***
image: {
toolbar: [
'toggleImageCaption',
'imageTextAlternative',
'|',
569
'imageStyle:inline',
'imageStyle:wrapText',
'imageStyle:breakText',
'|',
'resizeImage'
]
},
***
};
***
CustomAdapter
En este apartado, vamos a conocer de manera básica cómo podemos crear un adaptador personalizado que es
otra de las formas que tenemos para manejar la carga de archivos empleando CKEditor.
La implementación que se comparte, fue realizada hace un tiempo y es una mera referencia para que entiendas
su uso, pero, no recomendaría su implementación a menos que sea necesario, la implementación que siempre
deberíamos emplear en la de Simple Upload Adapter.
La implementación es la siguiente:
class MyUploadAdapter {
constructor(loader) {
// The file loader instance to use during the upload.
this.loader = loader;
}
// Starts the upload process.
upload() {
return this.loader.file.then(
file =>
new Promise((resolve, reject) => {
this._initRequest();
this._initListeners(resolve, reject, file);
this._sendRequest(file);
})
);
}
// Aborts the upload process.
abort() {
if (this.xhr) {
this.xhr.abort();
}
}
570
// Initializes the XMLHttpRequest object using the URL passed to the constructor.
_initRequest() {
const xhr = (this.xhr = new XMLHttpRequest());
// Note that your request may look different. It is up to you and your editor
// integration to choose the right communication channel. This example uses
// a POST request with JSON as a data structure but your configuration
// could be different.
xhr.open(
"POST",
(window.Laravel.routeType == 'local' ? "/dashboard" : "") +
"/post/content_image",
true
);
xhr.responseType = "json";
}
// Initializes XMLHttpRequest listeners.
_initListeners(resolve, reject, file) {
const xhr = this.xhr;
const loader = this.loader;
const genericErrorText = `Couldn't upload file: ${file.name}.`;
xhr.addEventListener("error", () => reject(genericErrorText));
xhr.addEventListener("abort", () => reject());
xhr.addEventListener("load", () => {
const response = xhr.response;
// This example assumes the XHR server's "response" object will come with
// an "error" which has its own "message" that can be passed to reject()
// in the upload promise.
//
// Your integration may handle upload errors in a different way so make sure
// it is done properly. The reject() function must be called when the upload
fails.
if (!response || response.error) {
return reject(
response && response.error ?
response.error.message :
genericErrorText
);
}
// If the upload is successful, resolve the upload promise with an object
containing
// at least the "default" URL, pointing to the image on the server.
571
// This URL will be used to display the image in the content. Learn more in the
// UploadAdapter#upload documentation.
resolve({
default: response.default
});
});
// Upload progress when it is supported. The file loader has the #uploadTotal and
#uploaded
// properties which are used e.g. to display the upload progress bar in the editor
// user interface.
if (xhr.upload) {
xhr.upload.addEventListener("progress", evt => {
if (evt.lengthComputable) {
loader.uploadTotal = evt.total;
loader.uploaded = evt.loaded;
}
});
}
}
// Prepares the data and sends the request.
_sendRequest(file) {
// Prepare the form data.
const data = new FormData();
document.getElementById("path").value =
document.getElementById("path").value.replace(/^,/, '');
data.append("path", document.getElementById("path").value);
data.append("image", file);
data.append("_token", document.getElementById("token").value);
// Important note: This is the right place to implement security mechanisms
// like authentication and CSRF protection. For instance, you can use
// XMLHttpRequest.setRequestHeader() to set the request headers containing
// the CSRF token generated earlier by your application.
// Send the request.
this.xhr.send(data);
}
}
function MyCustomUploadAdapterPlugin(editor) {
editor.plugins.get( FileRepository ).createUploadAdapter = loader => {
// Configure the URL to the upload script in your back-end here!
572
return new MyUploadAdapter(loader);
};
}
Como puntos importantes, la creación de la instancia del componente, en la cual, inicializamos el componente de
loader, para poder personalizar el proceso de upload:
constructor(loader) {
// The file loader instance to use during the upload.
this.loader = loader;
}
La función de upload se invoca cuando desde CKEditor enviamos una imagen para su carga, ya sea por arrastrar
la misma o desde el menú, en este proceso, invocamos 3 métodos personalizados que nos permitirán realizar la
carga:
// Starts the upload process.
upload() {
return this.loader.file.then(
file =>
new Promise((resolve, reject) => {
this._initRequest();
this._initListeners(resolve, reject, file);
this._sendRequest(file);
})
);
}
Este método configura la URL, el tipo de respuesta esperado y el método empleado (XMLHttpRequest) para
realizar la conexión a Laravel, en el ejemplo compartido, puedes ver lógica adicional que como es preguntar por
el ambiente en el cual se ejecuta el proyecto en Laravel para variar la URL:
// Initializes the XMLHttpRequest object using the URL passed to the constructor.
_initRequest() {
***
}
Inicializamos un listener para escuchar sobre el proceso del upload, si se completa, aborta y ocurre algún error:
// Initializes XMLHttpRequest listeners.
_initListeners(resolve, reject, file) {
***
});
}
573
Este método se encarga de realizar el upload o carga del archivo al servidor, puedes utilizarlo para subir no
solamente la imagen si no, datos adicionales como se ejemplifica que pasamos un parámetro llamado path, que
sería utilizado para cargar la imagen en otra ubicación:
// Prepares the data and sends the request.
_sendRequest(file) {
***
}
Luego, cargamos el plugin anterior como parte de la solución de CKEditor:
plugins: [
MyCustomUploadAdapterPlugin,
CodeBlock,
Essentials,
***
Finalmente cargamos el plugin junto con la instancia de CKEditor con la función
MyCustomUploadAdapterPlugin.
Más información en:
https://ckeditor.com/docs/ckeditor5/latest/framework/deep-dive/upload-adapter.html
Enlaces de interés y errores comunes y páginas de apoyo
Al momento de emplear el plugin de CKEditor pueden acarrear muchos problemas, como el siguiente error:
ckeditor-duplicated-modules
https://ckeditor.com/docs/ckeditor5/latest/support/error-codes.html#error-ckeditor-duplicated-modules
Que puede deberse as instalado varias versiones del plugin, o que no ha sido removido el paquete anterior del
package-lock.json; para esto, se recomienda eliminar la carpeta de los módulos de node y el de
package-lock.json luego, ejecutar:
$ npm install
En la documentación oficial puedes ver la página oficial para crear un adaptador de upload personalizado:
https://ckeditor.com/docs/ckeditor5/latest/framework/deep-dive/upload-adapter.html
Código fuente del capitulo:
https://github.com/libredesarrollo/book-course-laravel-base-package-11
574
Capítulo 21: Pruebas
Las pruebas son una parte crucial en cualquier aplicación que vayamos a crear, sin importar la tecnología,
siempre es recomendable realizar pruebas automáticas para probar el sistema cuando se implementen nuevos
cambios; de esta forma nos ahorramos mucho tiempo ya que, no hay necesidad de realizar muchas de las
pruebas de manera manual si no, ejecutando un simple comando.
Las pruebas consisten en probar los componentes de manera individual; en el caso de la aplicación que hemos
construido, serían cada uno de los métodos de la API, al igual que cualquier otra dependencia de estos métodos;
de esta manera, cuando se ejecutan estas pruebas automatizadas, si la aplicación pasa todas las pruebas,
significa que no se encontraron errores, pero, si no pasa las pruebas, significa que hay que hacer cambios a nivel
de la aplicación o pruebas implementadas.
¿Por qué hacer pruebas?
Las pruebas ayudan a garantizar que su aplicación funcionará como se espera y a medida que la aplicación vaya
creciendo en módulos y complejidad, se puedan implementar nuevas pruebas y adaptar las actuales.
Es importante mencionar que las pruebas no son perfectas, es decir, que, aunque la aplicación pase todas las
pruebas no significa que la aplicación está libre de errores, pero sí es un buen indicador inicial de la calidad del
software. Además, el código comprobable es generalmente una señal de una buena arquitectura de software.
Las pruebas deben de formar parte del ciclo de desarrollo de la aplicación para garantizar su buen
funcionamiento ejecutando las mismas constantemente.
¿Qué probar?
Las pruebas deberían centrarse en probar pequeñas unidades de código de forma aislada o individual.
Por ejemplo, en una aplicación Laravel o una aplicación web en general:
● Controladores:
○ Respuestas de las vistas
○ Códigos de estados
○ Condiciones nominales (GET, POST, etc.) para una función de vista
● Formularios
● Funciones de ayuda individuales
En Laravel, de manera oficial tenemos dos maneras de emplear las pruebas, mediante Pest y PHPUnit.
Las pruebas son la base de las pruebas en Laravel, con las cuales podemos probar de forma aislada los métodos
que componen a nuestra aplicación.
Pruebas con Pest/PHPUnit
PHPUnit es uno de los frameworks para realizar pruebas en PHP y que vienen ya instalado por defecto en
Laravel, al ser el que ha formado parte de Laravel por más tiempo, es el que primero vamos a cubrir. Es
575
importante aclarar que, para seguir este apartado, debes de haber seleccionado PHPunit como ambiente de
testing al momento de crear el proyecto en Laravel.
Al momento de crear un proyecto en Laravel, ya automáticamente también crea una carpeta de tests, con esto,
puedes darte cuenta de lo importante que, son las pruebas, que, aunque no forman parte de desarrollo funcional
de una aplicación, si forma parte del ciclo de vida de esta y crear las mismas es evidencia de buenas prácticas en
la programación.
Como hemos comentado anteriormente, Laravel a partir de la versión 11, uno de los cambios más importantes es
su limpieza de carpetas, quitando archivos y carpetas para fusionarlos con otros o generar bajo demanda otros
como en el caso del api.php, pero, puedes ver que la carpeta de tests aún está presente apenas creamos el
proyecto, dando una total evidencia de la importancia de la creación de estas pruebas para todas las aplicaciones
que desarrollemos.
Por defecto, la carpeta de tests contiene dos carpetas:
● tests/Feature
● tests/Unit
Las pruebas unitarias son aquellas que probamos un módulo concreto de la aplicación, como dice su nombre es
una unidad, algo que no podemos dividir, por ejemplo, un facade, un modelo, un helper son candidatos de
pruebas unitarias, ya que tienen código aislado de la aplicación y no se comunican entre sí como en el caso de
los controladores o componentes, estas pruebas se almacenan en tests/Unit.
Mientras que la carpeta de tests/Feature es la empleada para realizar pruebas de integración, como lo son los
controladores o aquellos componentes que no son individuales como en el caso anterior si no, en donde ocurren
muchas cosas como conexiones la base de datos, emplear helpers, facades o similares, retornas jsons, vistas,
etc. Estas pruebas se conocen como pruebas de funciones en donde probamos bloques de códigos más grandes
y que usualmente resuelven una respuesta HTTP completa.
Por defecto, ya Laravel viene con algunas pruebas y archivos listos para usar, una de las pruebas de ejemplo es
el de ExampleTest.php y que trae el hola mundo para nuestra aplicación.
Independientemente si estás empleando Pest o PHPUnit, la lógica es la misma, lo que cambia es la sintaxis y
para ejecutar nuestras pruebas tenemos el comando de:
$ vendor/bin/phpunit
Para PHPUnit, o:
$ vendor/bin/pest
Para Pest, o más fácil:
$ php artisan test
Para cualquiera de los anteriores.
576
Adicionalmente, puedes crear un archivo .env.testing en la raíz de su proyecto para manejar las configuraciones
en ambiente prueba. Este archivo se utilizará en lugar del archivo .env cuando se ejecuten pruebas de Pest y
PHPUnit o se ejecuten comandos de Artisan con la opción --env=testing.
Figura: 21-1 Carpeta de tests
Para crear una prueba unitaria, tenemos el siguiente comando:
$ php artisan make:test <ClassTest>
En donde la prueba se colocará en la carpeta de tests/Feature:
O si deseas crear una prueba dentro de la carpeta tests/Unit, puedes usar la opción --unit al ejecutar el comando
make:test:
$ php artisan make:test <ClassTest> --unit
Más información en:
https://laravel.com/docs/master/testing
Entendiendo las pruebas
Para que se entienda de una manera menos abstracta el uso de las pruebas, vamos a crear un pequeño ejercicio
de operaciones matemáticas antes de empezar a crear pruebas para probar módulos de nuestra aplicación,
como serían en caso de los controladores, es decir, el esquema de request/response.
Para estas primeras pruebas, creemos un archivo de operaciones matemáticas como el siguiente:
app\Utils\MathOperations.php
class MathOperations
{
public function add($a, $b) {
return $a + $b;
}
577
public function subtract($a, $b) {
return $a - $b;
}
public function multiply($a, $b) {
return $a * $b;
}
public function divide($a, $b) {
return $a / $b;
}
}
Ahora vamos a generar nuestro primer archivo para la primera prueba unitaria mediante:
$ php artisan make:test MathOperationsTest --unit
Esto generará un archivo en:
tests/Unit/MathOperationsTest.php
En el cual, creamos unas funciones que permitan probar los métodos anteriores para realizar operaciones
matemáticas:
tests/Unit/MathOperationsTest.php
<?php
namespace Tests\Unit;
use PHPUnit\Framework\TestCase;
// use App\Utils\MathOperations;
class MathOperations
{
public function add($a, $b)
{
return $a + $b;
}
public function subtract($a, $b)
{
return $a - $b;
}
578
public function multiply($a, $b)
{
return $a * $b;
}
public function divide($a, $b)
{
return $a / $b;
}
}
class MathOperationsTest extends TestCase
{
// public function test_example(): void
// {
// $this->assertTrue(true);
// }
public function testAdd()
{
$mathOperations = new MathOperations();
$result = $mathOperations->add(2, 3);
$this->assertEquals(5, $result);
}
public function testSubtract()
{
$mathOperations = new MathOperations();
$result = $mathOperations->subtract(5, 3);
$this->assertEquals(2, $result);
}
public function testSubtraction()
{
$mathOperations = new MathOperations();
$result = $mathOperations->multiply(5, 3);
$this->assertEquals(15, $result);
}
public function testDivide()
{
$mathOperations = new MathOperations();
$result = $mathOperations->divide(8, 2);
$this->assertEquals(4, $result);
}
}
579
Para facilitar el ejercicio, copiamos el contenido de MathOperations dentro del archivo unitario.
En este ejemplo, tenemos cuatro métodos de prueba, uno por cada método definido en la clase auxiliar
MathOperations que permite probar las operaciones de suma, resta, multiplicación y división respectivamente y
con esto podemos apreciar el corazón de las pruebas que es mediante métodos assert o métodos de tipo
aserción:
● assertStatus: Verifica el código de estado en la respuesta.
● assertOk: Verifica si la respuesta obtenida es de tipo 200.
● assertJson: Verifica si la respuesta es de tipo JSON.
● assertRedirect: Verifica si la respuesta es una redirección.
● assertSee: Verifica en base a un string suministrador, si forma parte de la respuesta.
● assertDontSee: Verifica si el string suministrado no forma parte de la respuesta.
● assertViewIs: Verifica si la vista fue retornada por la ruta.
● assertValid: Verifica si no hay errores de validación en el formulario enviado.
Que no son más que condicionales con los cuales verificamos que se obtengan los resultados esperados, en
este ejemplo, se emplea el método assertEquals pero existen varios con un funcionamiento similar e iremos
viendo algunos de ellos a lo largo del capítulo.
Puedes ver la inmensa lista completa en:
https://laravel.com/docs/master/http-tests#response-assertions
Aunque no te preocupes por tener que aprenderlos todos, usualmente usamos unos pocos de ellos.
Finalmente, para ejecutar las pruebas unitarias, usamos el comando de:
$ vendor/bin/phpunit
Y deberíamos de ver una salida como la siguiente:
Time: 00:00.850, Memory: 42.50 MB
OK (29 tests, 65 assertions)
Si provocamos algún error en la la clase auxiliar, como sumar dos veces el mismo parámetro, ignorando el otro:
public function add($a, $b)
{
return $a + $a;
}
Y ejecutamos:
$ vendor/bin/phpunit
Veremos una salida como la siguiente:
580
/MathOperationsTest.php:47
FAILURES!
Tests: 29, Assertions: 65, Failures: 1.
Que indica claramente de que ocurrió un error.
Las pruebas unitarias no son infalibles, ya que, todo depende de las pruebas que ejecutemos, manteniendo el
mismo error que provocamos antes, si la prueba fuera como la siguiente:
public function testAdd()
{
$mathOperations = new MathOperations();
$result = $mathOperations->add(2, 2);
$this->assertEquals(4, $result);
}
Las pruebas pasarían:
Time: 00:00.733, Memory: 42.50 MB
OK (29 tests, 65 assertions)
Pero, claramente tenemos un problema en la definición de la clase auxiliar, por lo tanto, las pruebas no son
infalibles, son solamente un medio para verificar que no encontramos errores en la aplicación, pero no significa
que la aplicación está libre de errores, con esto, podemos tener un entendimiento bsasçio y necesario de cómo
funcionan las pruebas unitarias, con este ejemplo, podemos ahora a pasar a probar realmente módulos que
conforman la aplicación.
Peticiones HTTP
Nuestra aplicación está formada por controladores o similares que son consumidos mediante peticiones HTTP de
distintos tipos y es justamente lo que usualmente debemos de probar; las pruebas unitarias están formadas de
dos bloques principales, los métodos de aspiración y los métodos http, que en PHPUnit, tenemos un método por
cada método HTTP, es decir métodos get, post, put, patch, o delete; para poder enviar peticiones HTTP;
debemos de heredar de la clase:
use Tests\TestCase;
Que es provista por Laravel y no por PHPUnit que es la que hemos empleado hasta ahora:
// use PHPUnit\Framework\TestCase;
Creamos la prueba, que en este caso es de integración ya que, vamos a probar un controlador con todo lo que
este puede incluir:
$ php artisan make:test CategoryTest
581
Y ya tenemos una estructura como la siguiente:
tests/Feature/CategoryTest.php
<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
class CategoryTest extends TestCase
{
/**
* A basic feature test example.
*/
public function test_all(): void
{
$response = $this->get('/');
$response->assertStatus(200);
}
}
Puedes ver que Laravel al detectar que es una prueba de integración ya crea la prueba posibilitando peticiones
de tipo HTTP mediante el TestCase de Laravel y otras importaciones que emplearemos más adelante.
Una petición de tipo post() con pase de parámetros luce como:
function test_post()
{
$response = $this->withHeaders([
'X-Header' => 'Value',
'Accept' => 'application/json',
])->post('/api/category', ['title' => 'Cate 1', 'slug' => 'cate-1']);
dd($response->status());
$response->assertStatus(201);
}
Recomendaciones
Como recomendaciones, prueba cada método de la aplicación de forma aislada, verifica que los datos devueltos
sean correctos, coloca nombres descriptivos, no importan si son largos y utiliza los métodos de aserciones que
más se ajuste a lo que deseas probar.
582
Finalmente, el código que debes de probar es el propio, no tiene sentido crear pruebas para módulos del
framework o de terceros.
Si en algún momento alguna prueba te falla con algún error 500 o algo que quieras inspeccionar, puedes evaluar
la respuesta mediante el contenido:
dd($response);
o
dd($response->getContent());
Configurar base de datos para pruebas
Usualmente las pruebas unitarias se deben de realizar en una base de datos de prueba, que no sea la de
desarrollo y mucho menos la de producción, de momento, hemos estado empleando la base de datos que
empleamos en desarrollo, entonces, todas las operaciones realizadas por las pruebas persisten en la misma y
con esto, no tenemos un entorno controlado para hacer las pruebas, para establecer una base de datos paralela
para hacer las pruebas debemos de realizar una configuración desde el siguiente archivo:
phpunit.xml
***
<php>
<env name="APP_ENV" value="testing"/>
<env name="APP_MAINTENANCE_DRIVER" value="file"/>
<env name="BCRYPT_ROUNDS" value="4"/>
<env name="CACHE_STORE" value="array"/>
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
<env name="MAIL_MAILER" value="array"/>
<env name="PULSE_ENABLED" value="false"/>
<env name="QUEUE_CONNECTION" value="sync"/>
<env name="SESSION_DRIVER" value="array"/>
<env name="TELESCOPE_ENABLED" value="false"/>
</php>
***
Aquí puedes personalizar la base de datos a emplear, en este ejemplo, SQLite (DB_DATABASE) y que sea en
memoria (DB_CONNECTION), lo que significa que las operaciones a la base de datos se van a realizar en una base
de datos en memoria y no haciendo operaciones de lectura/escritura sobre la base de datos.
La versión en memoria (DB_DATABASE) de la base de datos SQLite (DB_CONNECTION) funcionará sólo cuando
estamos en entorno de pruebas. Se creará y almacenará en la memoria y luego dejará de existir tan pronto como
se cierre la conexión a la base de datos (Si configuramos los trait que veremos en el siguiente apartado).
583
Cuando tenemos muchos casos de prueba, es posible que la prueba se realice más lentamente cuando usamos
la base de datos que lee y escribe en el disco. El beneficio de la base de datos en memoria es la velocidad
porque la memoria puede funcionar más rápido que el disco.
Como recomendación, se debe de emplear una base de datos independiente para cada ambiente, al igual que no
usamos la base de datos de desarrollo (entiéndase los registros) en producción, no deberíamos de emplear la
base de datos de desarrollo al momento de hacer las pruebas para siempre trabajar en ambientes controlados y
evitar comparar listados vacíos o con formatos NO controlados desde las pruebas.
Esto significa que, al momento de crear las pruebas unitarias, debemos de ejecutar las migraciones, para ello,
podemos usar algún trait como:
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
Que permiten ejecutar las migraciones:
$ php artisan migrate
O
$ php artisan migrate:refresh
De manera programática (puedes consultar la definición de estas clases para más detalle).
Y alguna de estas, debemos de anexar a la prueba unitaria, por ejemplo:
// use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
class CategoryTest extends TestCase
{
use RefreshDatabase;
// use DatabaseMigrations;
}
Adicionalmente, debemos de generar la data de prueba que vamos a probar, por ejemplo, si es para obtener
todos los registros, podemos emplear algo como:
tests\Unit\CategoryTest.php
<?php
namespace Tests\Unit;
use App\Models\Category;
584
// use PHPUnit\Framework\TestCase;
use Tests\TestCase;
// use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
class CategoryTest extends TestCase
{
use RefreshDatabase;
// use DatabaseMigrations;
public function test_example(): void
{
$this->assertTrue(true);
}
public function testGetAllCategories()
{
Category::factory(10)->create();
$categories = Category::get()->toArray();
$response = $this->get('/api/category/all');
$response->assertStatus(200)
->assertJson($categories);
}
}
Que es un factory que creamos antes:
database\factories\CategoryFactory.php
<?php
namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;
class CategoryFactory extends Factory
{
public function definition(): array
{
// $name = $this->faker->name();
$name = $this->faker->sentence;
return [
'title' => $name,
'slug' => str($name)->slug(),
585
];
}
}
Es importante devolver la data en el formato esperado para hacer las comprobaciones, aunque al momento de
ejecutar el factory esto devuelve un listado de categorías:
Category::factory(10)->create();
Al ser en tipo colección, no se puede comparar, lo mismo sucede si empleamos:
$categories = Category::get();
Es necesario convertir la data a un array para que sea equivalente a la data retornada:
$categories = Category::get()->toArray();
El código auditable mediante pruebas unitarias es menos propenso a errores y es una señal de buena
programación, que sigue las directrices de código reutilizable y modular, por lo tanto, son buenas señales de
tener una buena arquitectura de software.
Otro factor importante es que, si al momento de enviar una petición aparece un error 500, puedes revisar el
archivo de logs de Laravel:
storage/logs/laravel.log
Por ejemplo, pueden ocurrir errores como el siguiente:
testing.ERROR: Route [login] not defined. {"exception":"[object]
Para que procese la petición como de tipo JSON, debemos de configurar la cabecera correspondiente en las
peticiones:
$response = $this->withHeaders([
'Accept' => 'application/json',
])***;
Prueba de solicitudes y respuestas HTTP
Las pruebas unitarias son esos desarrollos que son muy personalizables y en base a la inmensidad de métodos
de tipo aserción y la libertad que tenemos al momento de programar, puedes personalizarlos a gusto según tus
necesidades.
Api Rest con PHPUnit
Al ser el sistema de Rest Api más sencillo de probar, al ser respuestas de tipo JSON, vamos a empezar a
implementar algunas pruebas unitarias sobre la Rest Api.
586
Categorías
Creamos el archivo para manejar las pruebas unitarias para las categorías:
$ php artisan make:test CategoryTest
Para poder enviar peticiones HTTP; debemos de heredar de la clase:
use Tests\TestCase;
Obtener todas las categorías
El método para obtener todas las categorías queda como:
tests/Unit/CategoryTest.php
<?php
namespace Tests\Unit;
use App\Models\Category;
// use PHPUnit\Framework\TestCase;
use Tests\TestCase;
// use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
class CategoryTest extends TestCase
{
use RefreshDatabase;
// use DatabaseMigrations;
public function test_all()
{
Category::factory(10)->create();
$categories = Category::get()->toArray();
// dd($categories);
$response = $this->get('/api/category/all');
$response->assertStatus(200)
->assertJson($categories);
}
}
Obtener categoría por id y slug
En esta segunda prueba, verificamos la respuesta que debe de devolver en base a la búsqueda de una categoría
por el identificador, lo primero que hacemos es generar una categoría, luego, con ese identificador se consulta a
587
la rest api para obtener el detalle de la categoría que debe ser igual al almacenado en la base de datos de
prueba.
tests/Unit/CategoryTest.php
***
class CategoryTest extends TestCase
{
***
public function test_get_by_id(): void
{
Category::factory(1)->create();
$category = Category::first();
$response = $this->get('/api/category/'.$category->id);
// dd($category);
$response->assertStatus(200);
$response->assertJson([
'id' => $category->id,
'title' => $category->title,
'slug' => $category->slug
]);
}
public function test_get_by_slug(): void
{
Category::factory(1)->create();
$category = Category::first();
$response = $this->get('/api/category/slug/'.$category->slug);
// dd($category);
$response->assertStatus(200);
$response->assertJson([
'id' => $category->id,
'title' => $category->title,
'slug' => $category->slug
]);
}
}
También se verifica por el código de estado, que debe ser de tipo 200 y también generamos la prueba para el
slug que sigue la misma estructura.
Crear una tarea
En la siguiente prueba, vemos cómo probar un recurso de tipo POST para la creación de una publicación y
verificamos el código de estado de tipo 200 y la respuesta que debe ser en formato JSON y que debe ser igual a
la data suministrada en la creación.
588
<?php
***
class CategoryTest extends TestCase
{
use DatabaseMigrations;
***
public function test_post(): void
{
$response = $this->withHeaders([
'Accept' => 'application/json'
])
->post('/api/category',['title' => 'Cate 1', 'slug'=>'cate-2-new']);
$response->assertStatus(200);
}
}
Editar una categoría
La siguiente prueba consiste en actualizar una categoría, la cual, es una combinación entre la de creación y
obtener el detalle de una categoría:
<?php
***
class CategoryTest extends TestCase
{
public function test_put(): void
{
Category::factory(1)->create();
$categoryOld = Category::first();
$dataEdit = ['title' => 'Cate new 1', 'slug'=>'cate-1-new'];
$response = $this->withHeaders([
'Accept' => 'application/json'
])
->put('/api/category/'.$categoryOld->id,$dataEdit);
$response->assertStatus(200)->assertJson($dataEdit);
}
}
En este punto, puedes darte cuenta que la prueba más complicada siempre son las primeras, a partir de ellas,
podemos emplear gran parte de su estructura para probar otros módulos.
Eliminar una categoría
Finalmente, la prueba para eliminar una tarea:
589
public function test_delete(): void
{
Category::factory(1)->create();
$category = Category::first();
$response = $this->delete('/api/category/' . $category->id);
$response->assertStatus(200)
->assertContent('"ok"');
$category = Category::find($category->id);
// $this->assertEquals($category==null,true);
$this->assertEquals($category,null);
}
Hay varias consideraciones importantes, lo primero, es que, se emplea el método de tipo assertContent() en vez
del de assertJson() ya que este último recibe un array como parámetro, por lo demás, el método
assertContent() busca coincidencias exactas.
Finalmente, empleamos el método de assertEquals() no desde la respuesta, ya que no queremos evaluar la
respuesta de forma directa si no la operación realizada mediante la misma, que es, que la categoría sea haya
eliminado; el método assertEquals() recibe como parámetro el valor y como siguiente parámetro la condición, en
el método anterior, puedes ver dos posibles usos.
Errores de validación
Debemos de realizar pruebas no solamente para escenarios exitosos, sino también, para cuando la respuesta no
es exitosa, por ejemplo, errores de validación, en este primer ejemplo, por el título:
public function test_post_error_form_title(): void
{
$data = ['title' => '', 'slug' => 'cate-2-new'];
$response = $this->withHeaders([
'Accept' => 'application/json'
])
->post('/api/category', $data);
$response->assertStatus(422);
// dd($response->getContent());
$this->assertStringContainsString("The title field is
required.",$response->getContent());
}
Como consideración importante, empleamos el método de assertStringContainsString() para buscar que en la
respuesta este el error de validacion de "The title field is required.", y no una comprobación exacta mediante el
método de assertContent() ya que este último busca coincidencias exactas, y los errores de validaciones tienen
una estructura un poco compleja, además de que es posible que a futuro queramos modificar la entidad de
categoría para agregar o remover campos y si hacemos una comprobación con un valor exacto, puede que varía
la respuesta y con esto falle la prueba, y el propósito de la prueba es SOLO verificar si la validación del título
requerido está presente.
590
Si te interesa probar la estructura exacta del error, puedes crear otra prueba.
Vamos a crear más pruebas para las validaciones, las categorías al ser una estructura sencilla, tenemos unas
pocas pruebas que podemos crear:
1. Validar título requerido.
2. Validar slug requerido.
3. Validar slug único.
Ya la primera la probamos, estos son unos casos solamente y puedes crear tantos como requieres, veamos dos
pruebas para los otros dos casos:
public function test_post_error_form_slug(): void
{
$data = ['title' => 'cate 3', 'slug' => ''];
$response = $this->withHeaders([
'Accept' => 'application/json'
])
->post('/api/category', $data);
$response->assertStatus(422);
$this->assertStringContainsString("The slug field is
required.",$response->getContent());
}
public function test_post_error_form_slug_unique(): void
{
Category::create(
[
'title' => 'category title', 'slug' => 'cate-3'
]
);
$data = ['title' => 'cate 3', 'slug' => 'cate-3'];
$response = $this->withHeaders([
'Accept' => 'application/json'
])
->post('/api/category', $data);
$response->assertStatus(422);
$this->assertStringContainsString("The slug has already been
taken.",$response->getContent());
}
Ha otro metodo de aserción que funciona de manera similar al assertStringContainsString() pero, este busca
coincidencias en un array, es decir, como parámetro para evaluar la contención, es un array:
$testArray = array("a"=>"value a", "b" =>"value b");
$value = "value b";
591
$this->assertContains($value, $testArray) ;
Categorías que no existan
También podemos implementar algunas pruebas para verificar que el detalle devuelve una página de 404 para
cuando el identificador de la categoría no exista:
public function test_get_by_id_404(): void
{
$response = $this->withHeaders([
'Accept' => 'application/json'
])->get('/api/category/1000');
$response->assertStatus(404)->assertContent('"Not found"');
}
public function test_get_by_slug_404(): void
{
$response = $this->withHeaders([
'Accept' => 'application/json'
])->get('/api/category/slug/cate-not-exist');
$response->assertStatus(404)->assertContent('"Not found"');
}
Post
Ahora, crearemos las pruebas para los posts; ejecutamos el siguiente comando:
$ php artisan make:test PostTest
tests/Feature/PostTest.php
Con el siguiente contenido:
<?php
namespace Tests\Feature;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
class PostTest extends TestCase
{
use RefreshDatabase;
public function test_all(): void
592
{
Category::factory(3)->create();
Post::factory(10)->create();
$posts = Post::get()->toArray();
// dd($categories);
$response = $this->get('/api/post/all');
// dd($response);
$response->assertStatus(200);
$response->assertJson($posts);
}
public function test_get_by_id(): void
{
Category::factory(3)->create();
Post::factory(1)->create();
$post = Post::first();
$response = $this->get('/api/post/' . $post->id);
$response->assertStatus(200);
$response->assertJson([
'id' => $post->id,
'title' => $post->title,
'slug' => $post->slug,
'content' => $post->content,
'category_id' => $post->category_id,
'description' => $post->description,
'posted' => $post->posted,
'updated_at' => $post->updated_at->toISOString(),
'created_at' => $post->created_at->toISOString(),
'image' => $post->image
]);
}
public function test_get_by_id_404(): void
{
$response = $this->withHeaders([
'Accept' => 'application/json'
])->get('/api/post/1000');
$response->assertStatus(404)->assertContent('"Not found"');
}
public function test_get_by_slug_404(): void
{
$response = $this->withHeaders([
593
'Accept' => 'application/json'
])->get('/api/post/slug/post-not-exist');
$response->assertStatus(404)->assertContent('"Not found"');
}
public function test_post(): void
{
Category::factory(1)->create();
$data = [
'title' => 'Post 1',
'slug' => 'post-1',
'content' => 'Content',
'description' => 'Description',
'image' => 'test.png',
'category_id' => 1,
'posted' => 'yes'
];
$response = $this->withHeaders([
'Accept' => 'application/json'
])->post('/api/post', $data);
$post = Post::find(1);
$response->assertStatus(200)->assertJson(
[
'title' => $post->title,
'slug' => $post->slug,
'content' => $post->content,
'category_id' => $post->category_id,
'description' => $post->description,
'posted' => $post->posted,
'updated_at' => $post->updated_at->toISOString(),
'created_at' => $post->created_at->toISOString(),
// 'image' => $post->image,
'id' => $post->id,
]
);
}
public function test_post_error_form_title(): void
{
$data = [
'title' => '',
594
'slug' => 'post-1',
'content' => 'Content',
'description' => 'Description',
'image' => 'test.png',
'category_id' => 1,
'posted' => 'yes'
];
$response = $this->withHeaders([
'Accept' => 'application/json'
])
->post('/api/post', $data);
$response->assertStatus(422);
$this->assertStringContainsString("The title field is
required.",$response->getContent());
}
public function test_post_error_form_slug(): void
{
$data = [
'title' => 'Post 1',
'slug' => '',
'content' => 'Content',
'description' => 'Description',
'image' => 'test.png',
'category_id' => 1,
'posted' => 'yes'
];
$response = $this->withHeaders([
'Accept' => 'application/json'
])
->post('/api/post', $data);
$response->assertStatus(422);
$this->assertStringContainsString("The slug field is
required.",$response->getContent());
}
public function test_post_error_form_slug_unique(): void
{
Category::factory(1)->create();
Post::create(
[
'title' => 'Post 1',
'slug' => 'post-1',
'content' => 'Content',
595
'description' => 'Description',
// 'image' => 'test.png',
'category_id' => 1,
'posted' => 'yes'
]
);
$data = [
'title' => 'New Post',
'slug' => 'post-1',
'content' => 'Content content',
'description' => 'Description',
'category_id' => 1,
'posted' => 'not'
];
$response = $this->withHeaders([
'Accept' => 'application/json'
])
->post('/api/post', $data);
$response->assertStatus(422);
$this->assertStringContainsString("The slug has already been taken.",
$response->getContent());
}
public function test_put(): void
{
Category::factory(3)->create();
Post::factory(1)->create();
$postOld = Post::first();
$dataEdit = [
'title' => 'Post new 1',
'slug' => 'post-new-1',
'content' => 'Content',
'description' => 'Description',
// 'image' => 'test.png',
'category_id' => 1,
'posted' => 'yes'
];
$response = $this->withHeaders([
'Accept' => 'application/json'
])
->put('/api/post/' . $postOld->id, $dataEdit);
596
$response->assertStatus(200)->assertJson($dataEdit);
}
public function test_put_error_form_img(): void
{
Category::factory(3)->create();
Post::factory(1)->create();
$postOld = Post::first();
$dataEdit = [
'title' => 'Post new 1',
'slug' => 'post-new-1',
'content' => 'Content',
'description' => 'Description',
'image' => 'test.png',
'category_id' => 1,
'posted' => 'yes'
];
$response = $this->withHeaders([
'Accept' => 'application/json'
])
->put('/api/post/' . $postOld->id, $dataEdit);
$response->assertStatus(422);
$this->assertStringContainsString("The image field must be a file of type: jpeg,
jpg, png.", $response->getContent());
}
public function test_delete(): void
{
Category::factory(3)->create();
Post::factory(1)->create();
$post = Post::first();
$response = $this->delete('/api/post/' . $post->id);
$response->assertStatus(200)
->assertContent('"ok"');
$post = Post::find($post->id);
// $this->assertEquals($post==null,true);
$this->assertEquals($post,null);
}
}
597
Puntos importante
En la creación, buscamos la publicación desde la base de datos para poder comparar la fecha, que se genera a
partir del tiempo en el cual se cree la publicación:
[{
"id": 1,
"title": "Post 1",
"slug": "post-1",
"description": "Description",
"content": "Content",
"image": null,
"category_id": 1,
"created_at": "2024-06-28T09:57:43.000000Z",
"updated_at": "2024-06-28T09:57:43.000000Z"
}]
[{
"title": "Post 1",
"slug": "post-1",
"content": "Content",
"category_id": 1,
"description": "Description",
"posted": "yes",
"updated_at": "2024-06-28T09:57:43.000000Z",
"created_at": "2024-06-28T09:57:43.000000Z",
"id": 1
}]
Como consideración, dependiendo de qué campos tengas en la publicación, puede que tengas que adaptar la
estructura (los campos, que deben ser los mismos, aunque los puedes colocar en cualquier orden).
La imagen no es suministrada al momento de editar, ya que, espera un campo de tipo archivo, si le pasamos otra
cosa, como un texto, daría un error como el siguiente:
{"message":"The image field must be a file of type: jpeg, jpg,
png.","errors":{"image":["The image field must be a file of type: jpeg, jpg, png."]}}
Es importante aclarar que en cada prueba que empleamos el Facade para los posts, generamos también las
categorías asociadas a la prueba, desde el Facade de los posts, NO generamos las posibles categorías, que en
nuestro ejemplo, son 3:
class PostFactory extends Factory
{
public function definition(): array
{
***
598
return [
'category_id' => $this->faker->randomElement([1, 2, 3]),
]
}
}
Recuerda que la base de datos está completamente limpia cada vez que se ejecuta las pruebas y es destruida al
final de las mismas, por lo tanto, puedes establecer identificadores foráneos como hicimos con la categoría sin
ningún miedo a que no exista (tras previa creación de la entidad relacional).
Módulo de usuario
Otro módulo que no puede faltar, es el del usuario, con la autenticación y generación del token, destrucción del
mismo; además, de poder emplear el token de acceso, para poder emplear ese token en recursos protegidos.
Ahora, crearemos las pruebas para los usuarios; ejecutamos el siguiente comando:
$ php artisan make:test UserTest
Login y generar el token
La prueba para el login queda como:
tests/Feature/UserTest.php
<?php
namespace Tests\Feature;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;
use App\Models\User;
class UserTest extends TestCase
{
use RefreshDatabase;
public function test_login(): void
{
User::factory()->create();
$user = User::first();
$data = [
'email' => $user->email,
'password' => 'password',
];
599
$response = $this->withHeaders([
'Accept' => 'application/json'
])->post('/api/user/login', $data);
$response->assertStatus(200);
$response->assertJsonStructure([
'isLoggedIn',
'token',
'user',
]);
}
}
Utilizamos el assertJsonStructure() para evaluar la estructura de la respuesta, al tener el token el texto plano
como respuesta y al ser los tokens diferentes en cada generación o login, no es posible comparar la respuesta
como lo hemos comparado antes, así que, solamente se compara la estructura.
Logout
La prueba para el logout para cerrar la sesión, queda como:
tests/Feature/UserTest.php
***
class UserTest extends TestCase
{
***
public function test_logout(): void
{
// create user factory
User::factory()->create();
$user = User::first();
$data = [
'email' => $user->email,
'password' => 'password',
];
$this->withHeaders([
'Accept' => 'application/json'
])->post('/api/user/login', $data);
$response = $this->withHeaders([
'Accept' => 'application/json',
])->post('/api/user/logout');
600
$response->assertStatus(200);
$this->assertEquals(count(User::first()->tokens), 0);
}
}
En esta primera prueba, utilizamos el recurso de login probado antes para hacer el login para luego poder cerrar
la sesión; en este ejemplo, no le pasamos el token de autenticación, en su lugar, empleamos la sesión de
Sanctum (Autenticación vía SPA que presentamos antes); finalmente, una vez realizado el logout mediante el
recurso, buscamos en la base datos que no existan tokens para el usuario.
También podemos implementar la prueba de logout creando el token en la misma prueba, de esta forma, el
ambiente queda más controlado:
public function test_logout2(): void
{
// create user factory
User::factory()->create();
$user = User::first();
$token = $user->createToken('myapptoken')->plainTextToken;
$response = $this->withHeaders([
'Accept' => 'application/json',
'Authorization' => 'Bearer ' . $token
])->post('/api/user/logout');
$response->assertStatus(200);
$this->assertEquals(count(User::first()->tokens), 0);
// $token = User::first()->tokens[0];
}
Verificar el token
La prueba para verificar que el token es válido:
public function test_check_token(): void
{
User::factory()->create();
$user = User::first();
$token = $user->createToken('myapptoken')->plainTextToken;
$response = $this->withHeaders([
'Accept' => 'application/json',
'Authorization' => 'Bearer '.$token
])->post('/api/user/token-check', ['token' => $token]);
601
$response->assertStatus(200);
$response->assertJson([
'isLoggedIn' => true,
'token' => $token
]);
}
Login incorrecto
Creamos una prueba en donde pasamos credenciales incorrectas:
public function test_login_incorrect(): void
{
// create user factory
User::factory()->create();
$user = User::first();
$data = [
'email' => $user->email,
'password' => 'invalid-password',
];
$response = $this->withHeaders([
'Accept' => 'application/json'
])->post('/api/user/login', $data);
$response->assertStatus(422);
}
Token incorrecto
Creamos una prueba en donde pasamos un token invalido:
public function test_check_invalid_token(): void
{
// create user factory
User::factory()->create();
$user = User::first();
$response = $this->withHeaders([
'Accept' => 'application/json',
])->post('/api/user/token-check', ['token' => 'tokeninvalido']);
$response->assertStatus(422)->assertContent('"Invalid user"');
602
}
Estas pruebas de errores de validación también son importantes ya que, debemos de probar tanto los casos
exitosos como los errores de validaciones y probar todas las bifurcaciones posibles de nuestra aplicación.
Consumir token desde recursos protegidos
En el caso de que tengas recursos protegidos por la autenticación requerida, en la clase a clase TestCase
implementamos un método que permita generar el token del usuario:
tests/TestCase.php
abstract class TestCase extends BaseTestCase
{
function generateTokenAuth()
{
User::factory()->create();
return User::first()->createToken('myapptoken')->plainTextToken;
}
}
Que luego es consumido al momento de hacer la petición a los recursos protegidos:
routes\api.php
Route::group(['middleware' => 'auth:sanctum'], function () {
Route::resource('category', CategoryController::class)->except(['create', 'edit']);
Route::resource('post', PostController::class)->except(['create', 'edit']);
});
Por ejemplo:
***
class CategoryTest extends TestCase
{
***
public function test_put(): void
{
***
$response = $this->withHeaders([
'Accept' => 'application/json',
'Authorization' => 'Bearer ' . $this->generateTokenAuth()
])
->put('/api/category/' . $categoryOld->id, $dataEdit);
$response->assertStatus(200)->assertJson($dataEdit);
603
}
public function test_delete_auth(): void
{
***
$response = $this->withHeaders([
'Accept' => 'application/json',
'Authorization' => 'Bearer ' . $this->generateTokenAuth()
])
->delete('/api/category/' . $category->id);
***
}
}
Organizar en carpetas tus pruebas
Si vas a tener varias pruebas con el mismo nombre pero que prueben diversos módulos por ejemplo:
app/Http/Controllers/Api/PostController.php
app/Http/Controllers/Dashboard/PostController.php
Puedes organizar tus pruebas en carpetas, por ejemplo, las que creamos antes, al ser para una API:
tests\Feature\Api\CategoryTest.php
tests\Feature\Api\PostTest.php
tests\Feature\Api\UserTest.php
Y recuerda cambiar los namespaces de las clases:
namespace Tests\Feature\Api;
App Web
Es momento de realizar pruebas al módulo web, que incluye el módulo de usuario, blog y dashboard, así que, en
esta oportunidad, no tenemos respuestas tan sencillas en formato JSON como en el caso anterior de la Rest API,
así que, comencemos.
Módulo de usuario
En este apartado, probaremos el módulo de login y cerrar sesión que recordemos que fueron generados
automáticamente por Laravel Breeze así que, las pruebas serán sencillas, aún así, se recomienda probarlas al
ser unas páginas que podemos personalizar a gusto y es importante verificar que al menos estén funcionando.
Creamos la prueba:
$ php artisan make:test Web/UserTest
604
Login
La primera prueba, es para verificar el login, que se divide en dos, la petición de tipo GET para pintar el
formulario y POST para realizar el login:
tests\Feature\Web\UserTest.php
<?php
namespace Tests\Feature\Web;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
class UserTest extends TestCase {
use DatabaseMigrations;
function test_login_get() {
$this->get('/login')
->assertStatus(200)
->assertSee('Forgot your password?')
->assertSee('Email')
->assertSee('Password');
}
}
El método de aserción assertSee() busca un texto en la respuesta, en este ejemplo, colocamos los textos más
importantes que deben de estar presente aunque, puedes personalizarlo a gusto.
Para la petición POST:
function test_login_post()
{
User::factory(1)->create();
$user = User::first();
$credentials = [
'email' => $user->email,
'password' => 'password',
];
$response = $this->post('/login', $credentials);
$response->assertRedirect('/dashboard');
$this->assertCredentials($credentials);
}
605
Al momento de hacer el login de manera exitosa, se hace una redirección a dashboard y mediante el método
assertCredentials() se verifica las credenciales que sean correctas.
También, puedes fusionar la petición de tipo GET con la de POST:
function test_login()
{
// get
$this->get('/login')
->assertStatus(200)
->assertSee('Forgot your password?')
->assertSee('Email')
->assertSee('Password');
// post
User::factory(1)->create();
$user = User::first();
$credentials = [
'email' => $user->email,
'password' => 'password',
];
$response = $this->post('/login', $credentials);
$response->assertRedirect('/dashboard');
$this->assertCredentials($credentials);
}
En este ejemplo, usamos un usuario generado mediante un facade, es importante que verifiques que solamente
se esté aplicando un hash al momento de especificar el password; para la versión que estoy empleando de
Laravel, por alguna razón aplica 2 hash, lo cual es un problema para cuando queremos hacer la autenticación:
app/Models/User.php
class User extends Authenticatable
{
***
public function setPasswordAttribute($value) {
$this->attributes['password'] = Hash::make($value);
}
protected function casts(): array
{
return [
606
'email_verified_at' => 'datetime',
'password' => 'hashed',
];
}
}
database/factories/UserFactory.php
public function definition(): array
{
return [
'name' => fake()->name(),
'email' => fake()->unique()->safeEmail(),
'email_verified_at' => now(),
'password' => 'password',
'remember_token' => Str::random(10),
];
}
En los códigos anteriores, fue necesario adaptarlos para que solamente aplique un hash y no convierta dos o tres
veces la contraseña a un hash y con esto, invalide su uso.
Finalmente, también verificamos que se ha hecho la redirección al dashboard una vez autenticado.
Login invalido
Al igual que antes, verificamos por errores de validación y/o error en el login:
function test_login_invalid()
{
User::factory(1)->create();
$user = User::first();
$credentials = [
'email' => $user->email,
'password' => 'invalid-password',
];
$response = $this->post('/login', $credentials);
$response->assertRedirect('/');
$this->assertInvalidCredentials($credentials);
}
607
Al momento de fallar el login mediante un password inválido, Laravel hace una autenticación a la raíz (/) como se
configuró en la prueba:
function test_login_invalid()
{
User::factory(1)->create();
$user = User::first();
$credentials = [
'email' => $user->email,
'password' => 'invalid-password',
];
$response = $this->post('/login', $credentials);
$response->assertRedirect('/');
$this->assertInvalidCredentials($credentials);
}
Con assertInvalidCredentials() hace la operación contraria a la de assertCredentials() y verifica credenciales
inválidas.
Registrar
Para el módulo de registrar, se siguen unos pasos similares a los realizados para hacer el login:
function test_register()
{
// get
$this->get('/register')
->assertStatus(200)
->assertSee('Already registered?')
->assertSee('Email')
->assertSee('Password');
// post
$data = [
'name' => 'Andres',
'email' => 'andres@gmail.com',
'password' => 'password',
'password_confirmation' => 'password',
];
$response = $this->post('/register', $data);
$response->assertRedirect('/dashboard');
$this->assertCredentials($data);
608
}
Al igual que antes, tenemos un doble hash para el password, así que, debemos deshabilitar alguna:
app/Http/Controllers/Auth/RegisteredUserController.php
***
class RegisteredUserController extends Controller
{
***
public function store(Request $request): RedirectResponse
{
***
$user = User::create([
'name' => $request->name,
'email' => $request->email,
// 'password' => Hash::make($request->password),
'password' => $request->password,
]);
***
}
}
app/Models/User.php
class User extends Authenticatable
{
***
public function setPasswordAttribute($value) {
$this->attributes['password'] = Hash::make($value);
}
}
Registro inválido
La prueba para registrar con errores queda de la siguiente manera:
function test_register_invalid_name()
{
// get para el redirect back al momento de los errores del form
$this->get('/register');
// post
$data = [
'name' => '',
'email' => 'andres@gmail.com',
'password' => 'password',
609
'password_confirmation' => 'password',
];
$response = $this->post('/register', $data);
$response->assertRedirect('/register');
$response->assertSessionHasErrors([
'name' => 'The name field is required.'
]);
}
Varias consideraciones importantes:
● Se debería de hacer varias pruebas para errores de formulario, como, que todos los campos estén
presente y que los campos tengan el formato esperado, por ejemplo, que los passwords coinciden, que el
campo email sea un email valido etc; estas pruebas quedan por parte del lector, aunque, al ser un recurso
existente generado desde Breeze la intención de estas pruebas es verificar que el recurso exista, por lo
tanto, en este caso en particular, no se recomendaría su implementación.
● Al ocurrir errores de validaciones, el método de validate, hace un redirect()->back() que redirecciona a la
página anterior, por lo tanto, antes de hacer el post invalido, primero se hace una petición GET para la
vista de registrar para que al momento de hacer el back() redirecciones también a la vista de registrar.
● El método assertSessionHasErrors() verifica si en la sesión flash existen los errores de validaciones,
puedes colocar más de uno:
○ $response->assertSessionHasErrors([
○ 'name' => 'The name field is required.'
○ 'email' => 'The email field is required.'
○ ]);
● Para el resto de las pruebas de errores en formulario en caso de que las quieras implementar, se sugiere
al lector que lo haga en métodos aparte, un método para verificar un email invalido, otro método para
comparar los password y otro por la longitud de los password y así.
Módulo blog
Para el módulo web, tenemos la página de listado y la de detalle.
Creamos la prueba:
$ php artisan make:test Web/BlogTest
El controlador se llama como BlogController por lo tanto, el nombre anterior es el sugerido.
Listado paginado
La prueba del listado paginado será similar al realizado en la API Rest, pero, la evaluación de la respuesta al no
se un JSON si no, un listado paginado, se emplean métodos específicos para tal fin:
tests\Feature\Web\BlogTest.php
<?php
namespace Tests\Feature\Web;
610
use App\Http\Controllers\blog\BlogController;
use App\Models\Post;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;
class BlogTest extends TestCase
{
use DatabaseMigrations;
function test_index()
{
// $response = $this->get('/blog')
$this
->get(route('blog.index'))
->assertStatus(200)
->assertViewIs('blog.index')
->assertSee('Post List')
->assertViewHas('posts', Post::paginate(2));
$this->assertInstanceOf(LengthAwarePaginator::class,$response->viewData('posts'));
}
}
En esta prueba, veremos varios aspectos interesantes, para variar, mostramos que también podemos emplear
una ruta con nombre:
->get(route('blog.index'))
Con este método de aserción, verificamos por el nombre de la vista, junto con su ruta:
->assertViewIs('blog.index')
Con este método, verificamos por la data suministrada a la vista y su nombre, que en este caso es el de posts,
que es el listado paginado para los posts:
->assertViewHas('posts', Post::paginate(2));
Con el siguiente método de aserción, obtenemos la data de la vista:
$response->viewData('posts')
Y verificamos que sea instancia de una clase, al estar empleando el:
611
Post::paginate(2)
Sabemos que es de LengthAwarePaginator:
$this->assertInstanceOf(LengthAwarePaginator::class,$response->viewData('posts'));
Esta prueba, que es nuestra primera prueba real sobre la app en Laravel que devuelve una vista, entiéndase un
contenido HTML generado con blade y no algo tan simple o plano como un JSON y con esto, podemos ver
métodos de aserción más específicos para garantizar que la data tenga el formato esperado, como puedes ver,
estas pruebas también sirven para especificar donde y como deben estar compuesta elementos como los datos,
vista y ruta, por lo tanto, al especificar una estructura clara, tienen sentido técnicas como la de TDD que en pocas
palabras, al momento de desarrollar un nuevo proyecto, primero se inicia con las pruebas y son las pruebas las
que especifican que es lo que se debe de implementar a nivel de funcionalidades.
Detalle
Para la prueba de detalle, es similar a la de listado, creando un post de ejemplo y haciendo las verificaciones
pertinentes como, formato de la data, ruta, vista empleada y data que tiene que estar presente:
function test_show()
{
// $response = $this->get('/blog')
Category::factory(3)->create();
User::factory(3)->create();
Post::factory(1)->create();
$post = Post::first();
$response = $this
->get(route('blog.show', ['post'=> $post]))
->assertStatus(200)
->assertViewIs('blog.show')
->assertSee($post->title)
->assertSee($post->content)
->assertSee($post->category->title)
->assertViewHas('post', $post);
$this->assertInstanceOf(Post::class,$response->viewData('post'));
}
Para esta prueba, el controlador que estamos probando luce de la siguiente forma:
app/Http/Controllers/blog/BlogController.php
function show(Post $post) {
return view('blog.show', ['post' => $post]);
}
612
Si quisieras probar el esquema de la caché:
app/Http/Controllers/blog/BlogController.php
function show(Post $post)
{
return cache()->rememberForever('post_show_' . $id, function () use ($id) {
$post = Post::with('category')->find($id);
return view('blog.show', ['post' => $post])->render();
});
}
Entonces, la prueba queda como:
function test_show_return_html_cache()
{
Category::factory(3)->create();
User::factory(3)->create();
Post::factory(1)->create();
$post = Post::first();
$html = view('blog.show', ['post' => $post])->render();
$response = $this
->get(route('blog.show', ['post'=> $post]))
// ->assertSee($html, escape:false)
->assertOk() // ->assertStatus(200)
->assertSee($post->title)
->assertSee($post->content)
->assertSee($post->category->title);
// dd($response->getContent());
$this->assertEquals($html, $response->getContent());
}
En la prueba anterior, quitamos todos los métodos de aserción que tengan que ver con la vista y el pase de
parámetros a la misma, además, de que, estamos empleamos el de assertEquals() para comparar el HTML que
es la respuesta del controlador cacheado; además de, que empleamos el método de assertOk() que es
equivalente al assertStatus(200).
{
613
$user = create('App\User', [
"email" => "user@mail.com"
]);
$credentials = [
"email" => "users@mail.com",
"password" => "secret"
];
$this->assertInvalidCredentials($credentials);
}
/** @test */
public function the_email_is_required_for_authenticate()
{
$user = create('App\User');
$credentials = [
"email" => null,
"password" => "secret"
];
$response = $this->from('/login')->post('/login', $credentials);
$response->assertRedirect('/login')->assertSessionHasErrors([
'email' => 'The email field is required.',
]);
}
/** @test */
public function the_password_is_required_for_authenticate()
{
$user = create('App\User', ['email' => 'zaratedev@gmail.com']);
$credentials = [
"email" => "zaratedev@gmail.com",
"password" => null
];
$response = $this->from('/login')->post('/login', $credentials);
$response->assertRedirect('/login')
->assertSessionHasErrors([
'password' => 'The password field is required.',
]);
}
}
public function testInvalidLogin() {
$response = $this->post('/login',[ 'email'=>'fake@email.com', 'password'=>'secret' ]);
$response->assertStatus(302);
614
$response->assertLocation('/login');
}
public function testInvalidLogin() {
$response = $this->post('/login',[ 'email'=>'fake@email.com', 'password'=>'secret'
]);
$response->assertStatus(302);
$response->assertLocation('/login');
}
Módulo dashboard
En este apartado, trataremos el módulo más grande y complejo como lo es, el módulo de gestión o de
dashboard.
CRUD para los posts
Comencemos con las pruebas para las publicaciones, creamos la prueba:
$ php artisan make:test Dashboard/PostTest
El controlador se llama como PostController por lo tanto, el nombre anterior es el sugerido.
Autenticación
El módulo de dashboard se encuentra protegido por autenticación requerida y por el sistema de roles y permisos;
pensando en esto, vamos a implementar el siguiente código:
tests/Feature/dashboard/PostTest.php
class PostTest extends TestCase
{
use DatabaseMigrations;
protected function setUp(): void
{
parent::setUp();
User::factory(1)->create();
$user = User::first();
// dd($user);
$role = Role::firstOrCreate(['name' => 'Admin']);
Permission::firstOrCreate(['name' => 'editor.post.index']);
Permission::firstOrCreate(['name' => 'editor.post.create']);
Permission::firstOrCreate(['name' => 'editor.post.edit']);
Permission::firstOrCreate(['name' => 'editor.post.destroy]);
$role->syncPermissions([1,2,3,4]);
615
$user->assignRole($role);
$this->actingAs($user);//->withSession(['role' => 'Admin']);
}
}
En el código anterior, usamos el método de setUp() que se ejecuta antes de cada prueba, en dicho método,
podemos colocar código común para ejecutar en cada una de las pruebas, en este ejemplo, la de crear el usuario
y establecer un rol de Admin y permisos.
Además, una vez configurado el usuario, configuramos la autenticación mediante actingAs() al cual, podemos
establecer datos en sesión en caso de que sea necesario.
Listado
La prueba de listado es muy similar a los generado en la prueba anterior, pero, cambiando los textos
establecidos:
tests/Feature/dashboard/PostTest.php
<?php
class PostTest extends TestCase
{
***
function test_index()
{
Category::factory(3)->create();
User::factory(3)->create();
Post::factory(20)->create();
$response = $this->get(route('post.index'))
->assertOk()
->assertViewIs('dashboard.post.index')
->assertSee('Dashboard')
->assertSee('Create')
->assertSee('Show')
->assertSee('Delete')
->assertSee('Edit')
->assertSee('Id')
->assertSee('Title')
->assertViewHas('posts', Post::paginate(10))
;
$this->assertInstanceOf(LengthAwarePaginator::class, $response->viewData('posts'));
}
}
616
Crear
La prueba de creación, la de gest, es muy similar al del formulario de login; además, evaluamos los datos
suministrados a la vista según hicimos antes:
tests/Feature/dashboard/PostTest.php
class PostTest extends TestCase
{
***
function test_create_get()
{
Category::factory(10)->create();
$response = $this->get(route('post.create'))
->assertOk()
->assertSee('Dashboard')
->assertSee('Title')
->assertSee('Slug')
->assertSee('Content')
->assertSee('Category')
->assertSee('Description')
->assertSee('Posted')
->assertSee('Send')
->assertViewHas('categories', Category::pluck('id', 'title'))
->assertViewHas('post', new Post())
;
$this->assertInstanceOf(Post::class,$response->viewData('post'));
$this->assertInstanceOf(Collection::class,$response->viewData('categories'));
}
}
Para el post queda como:
tests/Feature/dashboard/PostTest.php
function test_create_post(){
Category::factory(1)->create();
$data = [
'title' => 'Title',
'slug' => 'title',
'content' => 'Content',
'description' => 'Content',
'category_id' => 1,
'posted' => 'yes',
617
'user_id' => $this->user->id
];
$this->post(route('post.store', $data))
->assertRedirect(route('post.index'));
$this->assertDatabaseHas('posts', $data);
}
Se emplea el método de aserción assertDatabaseHas() como una posible implementación para verificar que la
data del post está en la tabla de posts, lo que significa que la prueba anterior para crear el post funciono.
Errores de validación en crear
Y de manera demostrativa, se realiza la prueba para verificar la regla de required para los campos del formulario:
tests/Feature/dashboard/PostTest.php
function test_create_post_invalid(){
Category::factory(1)->create();
$data = [
'title' => '',
'slug' => '',
'content' => '',
'description' => '',
// 'category_id' => 1,
'posted' => '',
];
$this->post(route('post.store', $data))
->assertRedirect('/')
->assertSessionHasErrors([
'title' => 'The title field is required.',
'slug' => 'The slug field is required.',
'content' => 'The content field is required.',
'description' => 'The description field is required.',
'posted' => 'The posted field is required.',
'category_id' => 'The category id field is required.',
]);
}
Es importante recordar, que se deberían de hacer pruebas para cada uno de las reglas de validación definidas, la
razón de esto no se debe a verificar el funcionamiento interno del framework, si no, que estemos empleando las
reglas requeridas para nuestras entidades:
public function rules(): array
618
{
return [
'title' => 'required|min:5|max:500',
'slug' => 'required|min:3|max:500|unique:posts',
'content' => 'required|min:7',
'category_id' => 'required|integer',
'description' => 'required|min:7',
'posted' => 'required',
];
}
Editar
Las pruebas para editar, son muy similares a la de creación, quedando como:
tests/Feature/dashboard/PostTest.php
function test_edit_get()
{
Category::factory(10)->create();
Post::factory(1)->create();
$post = Post::first();
$response = $this->get(route('post.edit', $post))
->assertOk()
->assertSee('Dashboard')
->assertSee('Title')
->assertSee('Slug')
->assertSee('Content')
->assertSee('Category')
->assertSee('Description')
->assertSee('Posted')
->assertSee('Send')
->assertSee($post->title)
->assertSee($post->content)
->assertSee($post->description)
->assertSee($post->slug)
->assertViewHas('categories', Category::pluck('id', 'title'))
->assertViewHas('post', $post);
$this->assertInstanceOf(Post::class, $response->viewData('post'));
$this->assertInstanceOf(Collection::class, $response->viewData('categories'));
}
function test_edit_put()
{
Category::factory(10)->create();
Post::factory(1)->create();
619
$post = Post::first();
$data = [
'title' => 'Title',
'slug' => 'title',
'content' => 'Content',
'description' => 'Content',
'category_id' => 1,
'posted' => 'yes'
];
$this->put(route('post.update', $post), $data)
->assertRedirect(route('post.index'));
$this->assertDatabaseHas('posts', $data);
$this->assertDatabaseMissing('posts', $post->toArray());
}
Errores de validación en editar
Al igual que para crear, creamos la prueba para cuando los datos suministrados son inválidos, en esta
oportunidad, para variar, hacemos también una petición GET, esto es para que, al momento de hacer el back por
los errores de validación, regrese a la ruta de editar:
tests/Feature/dashboard/PostTest.php
function test_edit_put_invalid()
{
Category::factory(10)->create();
Post::factory(1)->create();
$post = Post::first();
$this->get(route('post.edit', $post));
$data = [
'title' => 'a',
'slug' => '',
'content' => '',
'description' => '',
// 'category_id' => 1,
'posted' => '',
];
$this->put(route('post.update', $post), $data)
->assertRedirect(route('post.edit', $post))
->assertSessionHasErrors([
'title' => 'The title field must be at least 5 characters.',
620
'slug' => 'The slug field is required.',
'content' => 'The content field is required.',
'description' => 'The description field is required.',
'posted' => 'The posted field is required.',
'category_id' => 'The category id field is required.',
]);
}
Eliminar
La prueba para eliminar queda como:
tests/Feature/dashboard/PostTest.php
function test_edit_destroy()
{
Category::factory(10)->create();
Post::factory(1)->create();
$post = Post::first();
$data = [
'id' => $post->id
];
$this->delete(route('post.destroy', $post))
->assertRedirect(route('post.index'));
$this->assertDatabaseMissing('posts', $data);
}
CRUD para las categorías
Las pruebas para las categorías que es muy similar a las implementadas anteriormente, pero, quitando los
campos de content, slug, description, posted y category_id:
$ php artisan make:test Dashboard/CategoryTest
<?php
namespace Tests\Feature\dashboard;
use App\Models\User;
use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
621
use Tests\TestCase;
class CategoryTest extends TestCase
{
use DatabaseMigrations;
public User $user;
protected function setUp(): void
{
parent::setUp();
User::factory(1)->create();
$this->user = User::first();
$role = Role::firstOrCreate(['name' => 'Admin']);
Permission::firstOrCreate(['name' => 'editor.category.index']);
Permission::firstOrCreate(['name' => 'editor.category.create']);
Permission::firstOrCreate(['name' => 'editor.category.update']);
Permission::firstOrCreate(['name' => 'editor.category.destroy']);
$role->syncPermissions([1, 2, 3, 4]);
$this->user->assignRole($role);
$this->actingAs($this->user);
}
function test_index()
{
Category::factory(20)->create();
$response = $this->get(route('category.index'))
->assertOk()
->assertViewIs('dashboard.category.index')
->assertSee('Dashboard')
->assertSee('Create')
->assertSee('Show')
->assertSee('Delete')
->assertSee('Edit')
->assertSee('Id')
->assertSee('Title')
// ->assertViewHas('categories', Category::paginate(20))
;
$this->assertInstanceOf(LengthAwarePaginator::class,
$response->viewData('categories'));
622
}
function test_create_get()
{
$response = $this->get(route('category.create'))
->assertOk()
->assertSee('Dashboard')
->assertSee('Title')
->assertSee('Slug')
->assertSee('Send')
->assertViewHas('category', new Category());
}
function test_create_post()
{
$data = [
'title' => 'Title',
'slug' => 'title',
];
$this->post(route('category.store', $data))
->assertRedirect(route('category.index'));
$this->assertDatabaseHas('categories', $data);
}
function test_create_post_invalid()
{
$data = [
'title' => '',
'slug' => ''
];
$this->post(route('category.store', $data))
->assertRedirect('/')
->assertSessionHasErrors([
'title' => 'The title field is required.',
'slug' => 'The slug field is required.'
]);
}
function test_edit_get()
{
Category::factory(1)->create();
$category = Category::first();
$response = $this->get(route('category.edit', $category))
623
->assertOk()
->assertSee('Dashboard')
->assertSee('Title')
->assertSee('Slug')
->assertSee('Send')
->assertSee($category->title)
->assertSee($category->slug)
->assertViewHas('category', $category);
$this->assertInstanceOf(Category::class, $response->viewData('category'));
}
function test_edit_put()
{
Category::factory(1)->create();
$category = Category::first();
$data = [
'title' => 'Title',
'slug' => 'title'
];
$this->put(route('category.update', $category), $data)
->assertRedirect(route('category.index'));
$this->assertDatabaseHas('categories', $data);
$this->assertDatabaseMissing('categories', $category->toArray());
}
function test_edit_put_invalid()
{
Category::factory(1)->create();
$category = Category::first();
$this->get(route('category.edit', $category));
$data = [
'title' => 'a',
'slug' => ''
];
$this->put(route('category.update', $category), $data)
->assertRedirect(route('category.edit', $category))
->assertSessionHasErrors([
'title' => 'The title field must be at least 5 characters.',
'slug' => 'The slug field is required.'
]);
624
}
function test_edit_destroy()
{
Category::factory(1)->create();
$category = Category::first();
$data = [
'id' => $category->id
];
$this->delete(route('category.destroy', $category))
->assertRedirect(route('category.index'));
$this->assertDatabaseMissing('categories', $data);
}
}
CRUD para los roles
Las pruebas de roles son muy similar a las implementadas anteriormente, pero, cambiando el campo title por
name y removiendo el de slug, adicionalmente, empleamos un seeder para generar algunos roles de ejemplo:
$ php artisan make:test Dashboard/RoleTest
<?php
namespace Tests\Feature\dashboard;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
class RoleTest extends TestCase
{
use DatabaseMigrations;
public User $user;
protected function setUp(): void
{
parent::setUp();
625
User::factory(1)->create();
$this->user = User::first();
$role = Role::firstOrCreate(['name' => 'Admin']);
$this->user->assignRole($role);
$this->actingAs($this->user);
}
function test_index()
{
// Role::factory(20)->create();
$this->seed(RoleSeeder::class);
$response = $this->get(route('role.index'))
->assertOk()
->assertViewIs('dashboard.role.index')
->assertSee('Dashboard')
->assertSee('Create')
->assertSee('Show')
->assertSee('Delete')
->assertSee('Edit')
->assertSee('Id')
->assertSee('Name')
// ->assertViewHas('roles', Role::paginate(20))
;
$this->assertInstanceOf(LengthAwarePaginator::class, $response->viewData('roles'));
}
function test_create_get()
{
$response = $this->get(route('role.create'))
->assertOk()
->assertSee('Dashboard')
->assertSee('Name')
->assertSee('Send')
->assertViewHas('role', new Role());
}
function test_create_post()
{
$data = [
'name' => 'Name'
];
626
$this->post(route('role.store', $data))
->assertRedirect(route('role.index'));
$this->assertDatabaseHas('roles', $data);
}
function test_create_post_invalid()
{
$data = [
'name' => '',
];
$this->post(route('role.store', $data))
->assertRedirect('/')
->assertSessionHasErrors([
'name' => 'The name field is required.'
]);
}
function test_edit_get()
{
// Role::factory(1)->create();
$this->seed(RoleSeeder::class);
$role = Role::first();
$response = $this->get(route('role.edit', $role))
->assertOk()
->assertSee('Dashboard')
->assertSee('Name')
->assertSee('Send')
->assertSee($role->name)
->assertViewHas('role', $role);
$this->assertInstanceOf(Role::class, $response->viewData('role'));
}
function test_edit_put()
{
// Role::factory(1)->create();
$this->seed(RoleSeeder::class);
$role = Role::first();
$data = [
'name' => 'Name'
];
$this->put(route('role.update', $role), $data)
->assertRedirect(route('role.index'));
627
$this->assertDatabaseHas('roles', $data);
$this->assertDatabaseMissing('roles', $role->toArray());
}
function test_edit_put_invalid()
{
// Role::factory(1)->create();
$this->seed(RoleSeeder::class);
$role = Role::first();
$this->get(route('role.edit', $role));
$data = [
'name' => 'a',
];
$this->put(route('role.update', $role), $data)
->assertRedirect(route('role.edit', $role))
->assertSessionHasErrors([
'name' => 'The name field must be at least 3 characters.'
]);
}
function test_edit_destroy()
{
$this->seed(RoleSeeder::class);
$role = Role::first();
$data = [
'id' => $role->id
];
$this->delete(route('role.destroy', $role))
->assertRedirect(route('role.index'));
$this->assertDatabaseMissing('roles', $data);
}
}
No usamos los factories, debido a que el modelo de Rol no es propio, el factory no se asocia al mismo, el seeder
queda como:
database/seeders/RoleSeeder.php
<?php
628
namespace Database\Seeders;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
class RoleSeeder extends Seeder
{
/**
* Run the database seeds.
*/
public function run(): void
{
Role::create(['name' => 'Test 1']);
Role::create(['name' => 'Test 2']);
Role::create(['name' => 'Test 3']);
Role::create(['name' => 'Test 4']);
Role::create(['name' => 'Test 5']);
}
}
CRUD para los permisos
Las pruebas para los permisos que es muy similar a las implementadas anteriormente, pero, cambiando por el
modelo de permisos:
$ php artisan make:test Dashboard/PermissionTest
<?php
namespace Tests\Feature\dashboard;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
class PermissionTest extends TestCase
{
use DatabaseMigrations;
629
public User $user;
protected function setUp(): void
{
parent::setUp();
User::factory(1)->create();
$this->user = User::first();
$role = Role::firstOrCreate(['name' => 'Admin']);
$this->user->assignRole($role);
$this->actingAs($this->user);
}
function test_index()
{
// Permission::factory(20)->create();
$this->seed(PermissionSeeder::class);
$response = $this->get(route('permission.index'))
->assertOk()
->assertViewIs('dashboard.permission.index')
->assertSee('Dashboard')
->assertSee('Create')
->assertSee('Show')
->assertSee('Delete')
->assertSee('Edit')
->assertSee('Id')
->assertSee('Name')
// ->assertViewHas('permissions', Permission::paginate(20))
;
$this->assertInstanceOf(LengthAwarePaginator::class,
$response->viewData('permissions'));
}
function test_create_get()
{
$this->get(route('permission.create'))
->assertOk()
->assertSee('Dashboard')
->assertSee('Name')
->assertSee('Send')
->assertViewHas('permission', new Permission());
}
630
function test_create_post()
{
$data = [
'name' => 'Name'
];
$this->post(route('permission.store', $data))
->assertRedirect(route('permission.index'));
$this->assertDatabaseHas('permissions', $data);
}
function test_create_post_invalid()
{
$data = [
'name' => '',
];
$this->post(route('permission.store', $data))
->assertRedirect('/')
->assertSessionHasErrors([
'name' => 'The name field is required.'
]);
}
function test_edit_get()
{
// Permission::factory(1)->create();
$this->seed(PermissionSeeder::class);
$permission = Permission::first();
$response = $this->get(route('permission.edit', $permission))
->assertOk()
->assertSee('Dashboard')
->assertSee('Name')
->assertSee('Send')
->assertSee($permission->name)
->assertViewHas('permission', $permission);
$this->assertInstanceOf(Permission::class, $response->viewData('permission'));
}
function test_edit_put()
{
// Permission::factory(1)->create();
$this->seed(PermissionSeeder::class);
$permission = Permission::first();
631
$data = [
'name' => 'Name'
];
$this->put(route('permission.update', $permission), $data)
->assertRedirect(route('permission.index'));
$this->assertDatabaseHas('permissions', $data);
$this->assertDatabaseMissing('permissions', $permission->toArray());
}
function test_edit_put_invalid()
{
// Permission::factory(1)->create();
$this->seed(PermissionSeeder::class);
$permission = Permission::first();
$this->get(route('permission.edit', $permission));
$data = [
'name' => 'a',
];
$this->put(route('permission.update', $permission), $data)
->assertRedirect(route('permission.edit', $permission))
->assertSessionHasErrors([
'name' => 'The name field must be at least 3 characters.'
]);
}
function test_edit_destroy()
{
$this->seed(PermissionSeeder::class);
$permission = Permission::first();
$data = [
'id' => $permission->id
];
$this->delete(route('permission.destroy', $permission))
->assertRedirect(route('permission.index'));
$this->assertDatabaseMissing('permissions', $data);
}
}
632
CRUD para los usuarios
Las pruebas para los usuarios que es también muy similar a las implementadas anteriormente, pero, con los
campos de la entidad de usuario:
<?php
namespace Tests\Feature\dashboard;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
class UserTest extends TestCase
{
use DatabaseMigrations;
public User $user;
protected function setUp(): void
{
parent::setUp();
User::factory(1)->create();
$this->user = User::first();
$role = Role::firstOrCreate(['name' => 'Admin']);
Permission::firstOrCreate(['name' => 'editor.user.index']);
Permission::firstOrCreate(['name' => 'editor.user.create']);
Permission::firstOrCreate(['name' => 'editor.user.update']);
Permission::firstOrCreate(['name' => 'editor.user.destroy']);
$role->syncPermissions([1, 2, 3, 4]);
$this->user->assignRole($role);
$this->actingAs($this->user);
}
function test_index()
{
User::factory(20)->create();
$response = $this->get(route('user.index'))
->assertOk()
633
->assertViewIs('dashboard.user.index')
->assertSee('Dashboard')
->assertSee('Create')
->assertSee('Show')
->assertSee('Delete')
->assertSee('Edit')
->assertSee('Id')
->assertSee('Name')
// ->assertViewHas('users', User::paginate(20))
;
$this->assertInstanceOf(LengthAwarePaginator::class, $response->viewData('users'));
}
function test_create_get()
{
$this->get(route('user.create'))
->assertOk()
->assertSee('Dashboard')
->assertSee('Name')
->assertSee('Email')
->assertSee('Password')
->assertSee('Password Confirmation')
->assertSee('Send')
->assertViewHas('user', new User());
;
}
function test_create_post()
{
$data = [
'name' => 'andres',
'email' => 'userregular@gmail.com',
'password' => '&*FSDsdGDF1',
'password_confirmation' => '&*FSDsdGDF1'
];
$this->post(route('user.store', $data))
->assertRedirect(route('user.index'));
$this->assertDatabaseHas('users', [
'name' => 'andres',
'email' => 'userregular@gmail.com',
]);
}
634
function test_create_post_invalid()
{
$data = [
'name' => ''
];
$this->post(route('user.store', $data))
->assertRedirect('/')
->assertSessionHasErrors([
'name' => 'The name field is required.'
]);
$data = [
'name' => 'a',
'email' => 'andres',
'password' => '123',
'password_confirmation' => '1234',
];
$this->post(route('user.store', $data))
->assertRedirect('/')
->assertSessionHasErrors([
'name' => 'The name field must be at least 5 characters.',
'email' => 'The email field must be a valid email address.',
'password' => 'The password field confirmation does not match.',
// 'password' => 'The password field must be at least 8 characters.',
// 'password' => 'The password field must contain at least one uppercase
and one lowercase letter.',
// 'password' => 'The password field must contain at least one symbol.',
// 'password' => 'The password field must contain at least one number.',
]);
}
function test_edit_get()
{
User::factory(1)->create();
$user = User::first();
$response = $this->get(route('user.edit', $user))
->assertOk()
->assertSee('Dashboard')
->assertSee('Name')
->assertSee('Email')
->assertSee('Password')
->assertSee('Send')
->assertSee($user->name)
->assertSee($user->email)
635
->assertViewHas('user', $user);
$this->assertInstanceOf(User::class, $response->viewData('user'));
}
function test_edit_put()
{
User::factory(1)->create();
$user = User::first();
$data = [
'name' => 'New Name',
'email' => 'userregularnew@gmail.com',
'password' => 'new&*FSDsdGDF1',
'password_confirmation' => 'new&*FSDsdGDF1'
];
$this->put(route('user.update', $user), $data)
->assertRedirect(route('user.index'));
$this->assertDatabaseHas('users', [
'name' => 'New Name',
'email' => 'userregularnew@gmail.com',
]);
$this->assertDatabaseMissing('users', $user->toArray());
}
function test_edit_put_invalid()
{
User::factory(1)->create();
$user = User::first();
$this->get(route('user.edit', $user));
$data = [
'name' => 'a',
'email' => ''
];
$this->put(route('user.update', $user), $data)
->assertRedirect(route('user.edit', $user))
->assertSessionHasErrors([
'name' => 'The name field must be at least 5 characters.',
'email' => 'The email field is required.'
]);
$data = [
636
'name' => 'a',
'email' => 'andres',
'password' => '123',
'password_confirmation' => '1234',
];
$this->put(route('user.update', $user), $data)
->assertSessionHasErrors([
'name' => 'The name field must be at least 5 characters.',
'email' => 'The email field must be a valid email address.',
'password' => 'The password field confirmation does not match.',
// 'password' => 'The password field must be at least 8 characters.',
// 'password' => 'The password field must contain at least one uppercase
and one lowercase letter.',
// 'password' => 'The password field must contain at least one symbol.',
// 'password' => 'The password field must contain at least one number.',
]);
}
function test_edit_destroy()
{
User::factory(1)->create();
$user = User::first();
$data = [
'id' => $user->id
];
$this->delete(route('user.destroy', $user))
->assertRedirect(route('user.index'));
$this->assertDatabaseMissing('users', $data);
}
}
Pruebas con Pest/PHPUnit
Anteriormente creamos todas estas pruebas mediante PHPUnit, pero, en Laravel tenemos otro framework para
realizar las pruebas disponibles como lo es Pest; a términos prácticos, tienen más similitudes que diferencia y a
medida que vayamos traduciendo las pruebas realizadas anteriormente con PHPUnit, veremos como diferencia
fundamental, una sintaxis más simple, limpia y expresiva y en general, más moderna.
Es importante mencionar que, en un proyecto en Laravel, podemos emplear PHPUnit y/o Pest sin hacer ningún
cambio, el archivo clave en para PHPUnit es:
637
tests\TestCase.php
Y para Pest:
tests\Pest.php
Es importante también recordar que al momento de crear un proyecto en Laravel, se pregunta cuál framework
quieres emplear, y es es el empleado por defecto al momento de ejecutar el comando de:
$ laravel new <ProyectName>
También podemos especificar mediante una opción si queremos crear la prueba para PHPUnit:
$ php artisan make:test <Class>Test --phpunit
O Pest:
$ php artisan make:test <Class>Test --pest
Con Pest, no empleamos clases, tenemos métodos como el siguiente:
test('performs sums', function () {
$result = sum(1, 2);
expect($result)->toBe(3);
});
Como alternativa a la función test(), Pest proporciona la conveniente función it() que simplemente antepone la
descripción de la prueba con la palabra "it", haciendo que sus pruebas sean más legibles:
it('performs sums', function () {
$result = sum(1, 2);
expect($result)->toBe(3);
});
Finalmente, podemos agrupar pruebas relacionadas:
describe('sum', function () {
it('may sum integers', function () {
$result = sum(1, 2);
expect($result)->toBe(3);
});
it('may sum floats', function () {
$result = sum(1.5, 2.5);
638
expect($result)->toBe(4.0);
});
});
}
Más información en:
https://pestphp.com/docs/writing-tests
En todos los casos, en las funciones anteriores, definimos un texto representativo a la acción a probar mediante
la prueba, por ejemplo:
performs sums
Pruebas Unitarias con Pest
Las pruebas con Pest no cambian mucho de las implementadas con PHPUnit, es una sintaxis un poco más
simple y a continuación, se presenta las pruebas traducidas anteriormente con Pest:
tests/Feature/Api/CategoryTest.php
<?php
use App\Models\Category;
test('test all', function () {
Category::factory(10);
$categories = Category::get()->toArray();
$this->get(
'/api/category/all',
[
'Authorization' => 'Bearer ' . generateTokenAuth()
]
)->assertOk()->assertJson($categories);
});
it("test get by id", function () {
Category::factory(1)->create();
$category = Category::first();
$this->get('/api/category/' . $category->id, [
'Authorization' => 'Bearer ' . $this->generateTokenAuth()
])->assertStatus(200)->assertJson([
'id' => $category->id,
639
'title' => $category->title,
'slug' => $category->slug
]);
});
it("test get by slug", function () {
Category::factory(1)->create();
$category = Category::first();
$this->get('/api/category/slug/' . $category->slug, [
'Authorization' => 'Bearer ' . $this->generateTokenAuth()
])->assertStatus(200)->assertJson([
'id' => $category->id,
'title' => $category->title,
'slug' => $category->slug
]);
});
it("test post", function () {
$data = ['title' => 'Cate 1', 'slug' => 'cate-2-new'];
$this->post('/api/category', $data, [
'Accept' => 'application/json',
'Authorization' => 'Bearer ' . $this->generateTokenAuth()
])->assertStatus(200)->assertJson($data);
});
it("test post error form title", function () {
$data = ['title' => '', 'slug' => 'cate-2-new'];
$response = $this->post('/api/category', $data, [
'Accept' => 'application/json',
'Authorization' => 'Bearer ' . $this->generateTokenAuth()
])->assertStatus(422);
// $this->assertStringContainsString("The title field is required.",
$response->getContent());
$this->assertMatchesRegularExpression("/The title field is required./",
$response->getContent());
// $testArray = array("a"=>"value a", "b"=>"value b");
// $value = "value b";
// // assert function to test whether 'value' is a value of array
// $this->assertContains($value, $testArray) ;
// $this->assertContains("The title field is required.",['title'=>'["The title field is
required."]']);
});
it("test post error form slug", function () {
$data = ['title' => 'cate 3', 'slug' => ''];
$response = $this->post('/api/category', $data, [
'Accept' => 'application/json',
640
'Authorization' => 'Bearer ' . $this->generateTokenAuth()
])->assertStatus(422);
// $response->assertStringContainsString("The slug field is required.",
$response->getContent());
$this->assertMatchesRegularExpression("/The slug field is required./",
$response->getContent());
});
it("test post error form slug unique", function () {
Category::create(
[
'title' => 'category title',
'slug' => 'cate-3'
]
);
$data = ['title' => 'cate 3', 'slug' => 'cate-3'];
$response = $this->post('/api/category', $data, [
'Accept' => 'application/json',
'Authorization' => 'Bearer ' . $this->generateTokenAuth()
])->assertStatus(422);
$this->assertStringContainsString("The slug has already been taken.",
$response->getContent());
});
it("test get by id 404", function () {
$this->get('/api/category/1000', [
'Accept' => 'application/json',
'Authorization' => 'Bearer ' . $this->generateTokenAuth()
])->assertStatus(404)->assertContent('"Not found"');
});
it("test get by slug 404", function () {
$this->get('/api/category/slug/cate-not-exist', [
'Accept' => 'application/json',
])->assertStatus(404)->assertContent('"Not found"');
});
it("test put", function () {
Category::factory(1)->create();
$categoryOld = Category::first();
$dataEdit = ['title' => 'Cate new 1', 'slug' => 'cate-1-new'];
641
$this->put('/api/category/' . $categoryOld->id, $dataEdit, [
'Accept' => 'application/json',
'Authorization' => 'Bearer ' . $this->generateTokenAuth()
])->assertStatus(200)->assertJson($dataEdit);
});
it("test delete auth", function () {
Category::factory(1)->create();
$category = Category::first();
$this->delete('/api/category/' . $category->id,[], [
'Accept' => 'application/json',
'Authorization' => 'Bearer ' . $this->generateTokenAuth()
])->assertStatus(200)
->assertContent('"ok"');
$category = Category::find($category->id);
$this->assertEquals($category, null);
});
Al ser recursos protegidos por autenticación, definimos el método para generar el token dentro del a clase Pest:
tests/Pest.php
uses(TestCase::class, RefreshDatabase::class)->in('Feature');
function generateTokenAuth()
{
User::factory()->create();
return User::first()->createToken('myapptoken')->plainTextToken;
}
En el archivo anterior, también puedes ver que tienen el trait de RefreshDatabase que empleamos antes con
PHPUnit.
Para los posts, queda como:
tests/Feature/Pest/dashboard/PostTest.php
<?php
use App\Models\Category;
use App\Models\Post;
it("test all", function () {
Category::factory(3)->create();
642
Post::factory(10)->create();
$posts = Post::get()->toArray();
$this->get('/api/post/all')
->assertStatus(200)
->assertJson($posts);
});
it("test get by id", function () {
Category::factory(3)->create();
Post::factory(1)->create();
$post = Post::first();
$this->get('/api/post/' . $post->id)->assertStatus(200)->assertJson([
'id' => $post->id,
'title' => $post->title,
'slug' => $post->slug,
'content' => $post->content,
'category_id' => $post->category_id,
'description' => $post->description,
'posted' => $post->posted,
'updated_at' => $post->updated_at->toISOString(),
'created_at' => $post->created_at->toISOString(),
'image' => $post->image
]);
});
it("test get by id 404", function () {
$this->get('/api/post/1000', [
'Accept' => 'application/json'
])->assertStatus(404)->assertContent('"Not found"');
});
it("test get by slug 404", function () {
$this->get('/api/post/slug/post-not-exist', [
'Accept' => 'application/json'
])->assertStatus(404)->assertContent('"Not found"');
});
it("test post", function () {
Category::factory(1)->create();
$data = [
'title' => 'Post 1',
'slug' => 'post-1',
'content' => 'Content',
643
'description' => 'Description',
'image' => 'test.png',
'category_id' => 1,
'posted' => 'yes'
];
$response = $this->post('/api/post', $data, [
'Accept' => 'application/json'
]);
$post = Post::find(1);
$response->assertStatus(200)->assertJson(
[
'title' => $post->title,
'slug' => $post->slug,
'content' => $post->content,
'category_id' => $post->category_id,
'description' => $post->description,
'posted' => $post->posted,
'updated_at' => $post->updated_at->toISOString(),
'created_at' => $post->created_at->toISOString(),
// 'image' => $post->image,
'id' => $post->id,
]
);
});
it("test post error form title", function () {
$data = [
'title' => '',
'slug' => 'post-1',
'content' => 'Content',
'description' => 'Description',
'image' => 'test.png',
'category_id' => 1,
'posted' => 'yes'
];
$response = $this->post('/api/post', $data, [
'Accept' => 'application/json'
])->assertStatus(422);
$this->assertMatchesRegularExpression("/The title field is required./",
$response->getContent());
});
644
it("test post error form slug", function () {
$data = [
'title' => 'Post 1',
'slug' => '',
'content' => 'Content',
'description' => 'Description',
'image' => 'test.png',
'category_id' => 1,
'posted' => 'yes'
];
$response = $this->post('/api/post', $data, [
'Accept' => 'application/json'
])->assertStatus(422);
$this->assertMatchesRegularExpression("/The slug field is required./",
$response->getContent());
});
it("test post error form slug unique", function () {
Category::factory(1)->create();
Post::create(
[
'title' => 'Post 1',
'slug' => 'post-1',
'content' => 'Content',
'description' => 'Description',
// 'image' => 'test.png',
'category_id' => 1,
'posted' => 'yes'
]
);
$data = [
'title' => 'New Post',
'slug' => 'post-1',
'content' => 'Content content',
'description' => 'Description',
'category_id' => 1,
'posted' => 'not'
];
$response = $this->post('/api/post', $data, [
'Accept' => 'application/json'
])->assertStatus(422);
645
$this->assertMatchesRegularExpression("/The slug has already been taken./",
$response->getContent());
});
it("test put", function () {
Category::factory(3)->create();
Post::factory(1)->create();
$postOld = Post::first();
$dataEdit = [
'title' => 'Post new 1',
'slug' => 'post-new-1',
'content' => 'Content',
'description' => 'Description',
// 'image' => 'test.png',
'category_id' => 1,
'posted' => 'yes'
];
$this
->put('/api/post/' . $postOld->id, $dataEdit, [
'Accept' => 'application/json'
])->assertStatus(200)->assertJson($dataEdit);
});
it("test put error form img", function () {
Category::factory(3)->create();
Post::factory(1)->create();
$postOld = Post::first();
$dataEdit = [
'title' => 'Post new 1',
'slug' => 'post-new-1',
'content' => 'Content',
'description' => 'Description',
'image' => 'test.png',
'category_id' => 1,
'posted' => 'yes'
];
$response = $this->put('/api/post/' . $postOld->id, $dataEdit, [
'Accept' => 'application/json'
]);
646
$response->assertStatus(422);
$this->assertMatchesRegularExpression("/The image field must be a file of type: jpeg,
jpg, png./", $response->getContent());
});
it("test delete", function () {
Category::factory(3)->create();
Post::factory(1)->create();
$post = Post::first();
$this->delete('/api/post/' . $post->id)->assertStatus(200)->assertContent('"ok"');
$post = Post::find($post->id);
$this->assertEquals($post, null);
});
Y para los usuarios:
tests/Feature/Pest/dashboard/UserTest.php
<?php
use App\Models\User;
test('test login', function () {
// create user factory
User::factory()->create();
$user = User::first();
$data = [
'email' => $user->email,
'password' => 'password',
];
$this->post('/api/user/login', $data, [
'Accept' => 'application/json'
])->assertStatus(200)
->assertJsonStructure([
'isLoggedIn',
'token',
'user',
]);
});
test('test login incorrect', function () {
647
User::factory()->create();
$user = User::first();
$data = [
'email' => $user->email,
'password' => 'invalid-password',
];
$response = $this->post('/api/user/login', $data, [
'Accept' => 'application/json'
]);
$response->assertStatus(422);
});
test('test logout', function () {
User::factory()->create();
$user = User::first();
$data = [
'email' => $user->email,
'password' => 'password',
];
$this->post('/api/user/login', $data, [
'Accept' => 'application/json'
]);
$this->post('/api/user/logout', [], [
'Accept' => 'application/json'
])->assertStatus(200);
$this->assertEquals(count(User::first()->tokens), 0);
});
test('test logout2', function () {
User::factory()->create();
$user = User::first();
$token = $user->createToken('myapptoken')->plainTextToken;
$this->post('/api/user/logout', [], [
'Accept' => 'application/json',
'Authorization' => 'Bearer ' . $token
])->assertStatus(200);
$this->assertEquals(count(User::first()->tokens), 0);
});
648
test('test check token', function () {
// create user factory
User::factory()->create();
$user = User::first();
$token = $user->createToken('myapptoken')->plainTextToken;
$this->post('/api/user/token-check', ['token' => $token], [
'Accept' => 'application/json',
'Authorization' => 'Bearer ' . $token
])->assertStatus(200)->assertJson([
'isLoggedIn' => true,
'token' => $token
]);
});
test('test check invalid token', function () {
// create user factory
User::factory()->create();
$user = User::first();
$this->post('/api/user/token-check', ['token' => 'tokeninvalido'], [
'Accept' => 'application/json',
])->assertStatus(422)->assertContent('"Invalid user"');
});
<?php
use App\Models\User;
use App\Models\Category;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
beforeEach(function () {
User::factory(1)->create();
$this->user = User::first();
$role = Role::firstOrCreate(['name' => 'Admin']);
Permission::firstOrCreate(['name' => 'editor.category.index']);
Permission::firstOrCreate(['name' => 'editor.category.create']);
Permission::firstOrCreate(['name' => 'editor.category.update']);
Permission::firstOrCreate(['name' => 'editor.category.destroy']);
649
$role->syncPermissions([1, 2, 3, 4]);
$this->user->assignRole($role);
$this->actingAs($this->user);
});
test('test index', function () {
Category::factory(20)->create();
$response = $this->get(route('category.index'))
->assertOk()
->assertViewIs('dashboard.category.index')
->assertSee('Dashboard')
->assertSee('Create')
->assertSee('Show')
->assertSee('Delete')
->assertSee('Edit')
->assertSee('Id')
->assertSee('Title');
$this->assertInstanceOf(LengthAwarePaginator::class,
$response->viewData('categories'));
});
test('test create get', function () {
$response = $this->get(route('category.create'))
->assertOk()
->assertSee('Dashboard')
->assertSee('Title')
->assertSee('Slug')
->assertSee('Send')
->assertViewHas('category', new Category());
;
});
test('test create post', function () {
$data = [
'title' => 'Title',
'slug' => 'title',
];
$this->post(route('category.store', $data))
->assertRedirect(route('category.index'));
650
$this->assertDatabaseHas('categories', $data);
});
test('test create post invalid', function () {
$data = [
'title' => '',
'slug' => ''
];
$this->post(route('category.store', $data))
->assertRedirect('/')
->assertSessionHasErrors([
'title' => 'The title field is required.',
'slug' => 'The slug field is required.'
]);
});
test('test edit get', function () {
Category::factory(1)->create();
$category = Category::first();
$response = $this->get(route('category.edit', $category))
->assertOk()
->assertSee('Dashboard')
->assertSee('Title')
->assertSee('Slug')
->assertSee('Send')
->assertSee($category->title)
->assertSee($category->slug)
->assertViewHas('category', $category);
$this->assertInstanceOf(Category::class, $response->viewData('category'));
});
test('test edit put', function () {
Category::factory(1)->create();
$category = Category::first();
$data = [
'title' => 'Title',
'slug' => 'title'
];
651
$this->put(route('category.update', $category), $data)
->assertRedirect(route('category.index'));
$this->assertDatabaseHas('categories', $data);
$this->assertDatabaseMissing('categories', $category->toArray());
});
test('test edit put invalid', function () {
Category::factory(1)->create();
$category = Category::first();
$this->get(route('category.edit', $category));
$data = [
'title' => 'a',
'slug' => ''
];
$this->put(route('category.update', $category), $data)
->assertRedirect(route('category.edit', $category))
->assertSessionHasErrors([
'title' => 'The title field must be at least 5 characters.',
'slug' => 'The slug field is required.'
]);
});
test('test destroy', function () {
Category::factory(1)->create();
$category = Category::first();
$data = [
'id' => $category->id
];
$this->delete(route('category.destroy', $category))
->assertRedirect(route('category.index'));
$this->assertDatabaseMissing('categories', $data);
});
Las pruebas para el dashboard, la categoría:
652
tests/Feature/Pest/dashboard/CategoryTest.php
<?php
use App\Models\User;
use App\Models\Category;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
beforeEach(function () {
User::factory(1)->create();
$this->user = User::first();
$role = Role::firstOrCreate(['name' => 'Admin']);
Permission::firstOrCreate(['name' => 'editor.category.index']);
Permission::firstOrCreate(['name' => 'editor.category.create']);
Permission::firstOrCreate(['name' => 'editor.category.update']);
Permission::firstOrCreate(['name' => 'editor.category.destroy']);
$role->syncPermissions([1, 2, 3, 4]);
$this->user->assignRole($role);
$this->actingAs($this->user);
});
test('test index', function () {
Category::factory(20)->create();
$response = $this->get(route('category.index'))
->assertOk()
->assertViewIs('dashboard.category.index')
->assertSee('Dashboard')
->assertSee('Create')
->assertSee('Show')
->assertSee('Delete')
->assertSee('Edit')
->assertSee('Id')
->assertSee('Title');
$this->assertInstanceOf(LengthAwarePaginator::class,
$response->viewData('categories'));
653
});
test('test create get', function () {
$response = $this->get(route('category.create'))
->assertOk()
->assertSee('Dashboard')
->assertSee('Title')
->assertSee('Slug')
->assertSee('Send')
->assertViewHas('category', new Category());
;
});
test('test create post', function () {
$data = [
'title' => 'Title',
'slug' => 'title',
];
$this->post(route('category.store', $data))
->assertRedirect(route('category.index'));
$this->assertDatabaseHas('categories', $data);
});
test('test create post invalid', function () {
$data = [
'title' => '',
'slug' => ''
];
$this->post(route('category.store', $data))
->assertRedirect('/')
->assertSessionHasErrors([
'title' => 'The title field is required.',
'slug' => 'The slug field is required.'
]);
});
test('test edit get', function () {
Category::factory(1)->create();
$category = Category::first();
$response = $this->get(route('category.edit', $category))
654
->assertOk()
->assertSee('Dashboard')
->assertSee('Title')
->assertSee('Slug')
->assertSee('Send')
->assertSee($category->title)
->assertSee($category->slug)
->assertViewHas('category', $category);
$this->assertInstanceOf(Category::class, $response->viewData('category'));
});
test('test edit put', function () {
Category::factory(1)->create();
$category = Category::first();
$data = [
'title' => 'Title',
'slug' => 'title'
];
$this->put(route('category.update', $category), $data)
->assertRedirect(route('category.index'));
$this->assertDatabaseHas('categories', $data);
$this->assertDatabaseMissing('categories', $category->toArray());
});
test('test edit put invalid', function () {
Category::factory(1)->create();
$category = Category::first();
$this->get(route('category.edit', $category));
$data = [
'title' => 'a',
'slug' => ''
];
$this->put(route('category.update', $category), $data)
->assertRedirect(route('category.edit', $category))
->assertSessionHasErrors([
'title' => 'The title field must be at least 5 characters.',
655
'slug' => 'The slug field is required.'
]);
});
test('test destroy', function () {
Category::factory(1)->create();
$category = Category::first();
$data = [
'id' => $category->id
];
$this->delete(route('category.destroy', $category))
->assertRedirect(route('category.index'));
$this->assertDatabaseMissing('categories', $data);
});
En el ejemplo anterior, la función beforeEach() es el equivalente del de setUp() en PHPUnit empleado para
inicializar los datos de la prueba, en este ejemplo, el usuario autenticado.
Las pruebas para los posts:
<?php
use App\Models\User;
use App\Models\Post;
use App\Models\Category;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
beforeEach(function () {
User::factory(1)->create();
$this->user = User::first();
$role = Role::firstOrCreate(['name' => 'Admin']);
Permission::firstOrCreate(['name' => 'editor.post.index']);
Permission::firstOrCreate(['name' => 'editor.post.create']);
Permission::firstOrCreate(['name' => 'editor.post.update']);
656
Permission::firstOrCreate(['name' => 'editor.post.destroy']);
$role->syncPermissions([1, 2, 3, 4]);
$this->user->assignRole($role);
$this->actingAs($this->user);
});
test('test index', function () {
Category::factory(3)->create();
User::factory(3)->create();
Post::factory(20)->create();
$response = $this->get(route('post.index'))
->assertOk()
->assertViewIs('dashboard.post.index')
->assertSee('Dashboard')
->assertSee('Create')
->assertSee('Show')
->assertSee('Delete')
->assertSee('Edit')
->assertSee('Id')
->assertSee('Title')
// ->assertViewHas('posts', Post::paginate(10))
;
$this->assertInstanceOf(LengthAwarePaginator::class, $response->viewData('posts'));
});
test('test create get', function () {
Category::factory(10)->create();
$response = $this->get(route('post.create'))
->assertOk()
->assertSee('Dashboard')
->assertSee('Title')
->assertSee('Slug')
->assertSee('Content')
->assertSee('Category')
->assertSee('Description')
->assertSee('Posted')
->assertSee('Send')
->assertViewHas('categories', Category::pluck('id', 'title'))
->assertViewHas('post', new Post());
657
$this->assertInstanceOf(Post::class, $response->viewData('post'));
$this->assertInstanceOf(Collection::class, $response->viewData('categories'));
});
test('test create post', function () {
Category::factory(1)->create();
$data = [
'title' => 'Title',
'slug' => 'title',
'content' => 'Content',
'description' => 'Content',
'category_id' => 1,
'posted' => 'yes',
'user_id' => $this->user->id
];
$this->post(route('post.store', $data))
->assertRedirect(route('post.index'));
$this->assertDatabaseHas('posts', $data);
});
test('test create post invalid', function () {
Category::factory(1)->create();
$data = [
'title' => '',
'slug' => '',
'content' => '',
'description' => '',
// 'category_id' => 1,
'posted' => '',
];
$this->post(route('post.store', $data))
->assertRedirect('/')
->assertSessionHasErrors([
'title' => 'The title field is required.',
'slug' => 'The slug field is required.',
'content' => 'The content field is required.',
'description' => 'The description field is required.',
'posted' => 'The posted field is required.',
'category_id' => 'The category id field is required.',
]);
658
});
test('test edit get', function () {
Category::factory(10)->create();
Post::factory(1)->create();
$post = Post::first();
$response = $this->get(route('post.edit', $post))
->assertOk()
->assertSee('Dashboard')
->assertSee('Title')
->assertSee('Slug')
->assertSee('Content')
->assertSee('Category')
->assertSee('Description')
->assertSee('Posted')
->assertSee('Send')
->assertSee($post->title)
->assertSee($post->content)
->assertSee($post->description)
->assertSee($post->slug)
->assertViewHas('categories', Category::pluck('id', 'title'))
->assertViewHas('post', $post);
$this->assertInstanceOf(Post::class, $response->viewData('post'));
$this->assertInstanceOf(Collection::class, $response->viewData('categories'));
});
test('test edit put', function () {
Category::factory(10)->create();
Post::factory(1)->create();
$post = Post::first();
$data = [
'title' => 'Title',
'slug' => 'title',
'content' => 'Content',
'description' => 'Content',
'category_id' => 1,
'posted' => 'yes'
];
$this->put(route('post.update', $post), $data)
->assertRedirect(route('post.index'));
659
$this->assertDatabaseHas('posts', $data);
$this->assertDatabaseMissing('posts', $post->toArray());
});
test('test edit put invalid', function () {
Category::factory(10)->create();
Post::factory(1)->create();
$post = Post::first();
$this->get(route('post.edit', $post));
$data = [
'title' => 'a',
'slug' => '',
'content' => '',
'description' => '',
// 'category_id' => 1,
'posted' => '',
];
$this->put(route('post.update', $post), $data)
->assertRedirect(route('post.edit', $post))
->assertSessionHasErrors([
'title' => 'The title field must be at least 5 characters.',
'slug' => 'The slug field is required.',
'content' => 'The content field is required.',
'description' => 'The description field is required.',
'posted' => 'The posted field is required.',
'category_id' => 'The category id field is required.',
])
;
});
test('test destroy', function () {
Category::factory(10)->create();
Post::factory(1)->create();
$post = Post::first();
$data = [
'id' => $post->id
];
$this->delete(route('post.destroy', $post))
660
->assertRedirect(route('post.index'));
$this->assertDatabaseMissing('posts', $data);
});
Puedes consultar el resto de las pruebas que son equivalentes a la de Pest en:
https://github.com/libredesarrollo/book-course-laravel-base-11/releases/tag/v0.12
https://github.com/libredesarrollo/book-course-laravel-base-api-11/releases/tag/v0.4
¿Qué es TDD?
Como consideración adicional, hablamos sobre una técnica llamada Test Driven Development (TDD) en español,
Desarrollo basado en pruebas, también conocido como desarrollo guiado por pruebas, la cual es una práctica de
programación en la que se escriben pruebas antes de crear el código de la funcionalidad siguiendo los siguientes
aspectos claves:
1. Al definir primero las pruebas, permite especificar cada funcionalidad antes de escribir el código real, es
como una especie de árbol mental, pero con código, de esta forma, se guía el proceso de desarrollo.
2. Código limpio y robusto: El objetivo es crear un código limpio, robusto y simple. Si las pruebas fallan, se
corrigen los errores antes de avanzar.
Esto solo lo mencionamos para dar otro ejemplo de la importancia de las pruebas en el desarrollo de software y
por supuesto, que se aplica al desarrollo en Laravel.
661
Capítulo 22: Laravel a producción
Una vez terminada la implementación de tu estupendo proyecto en Laravel, seguramente lo siguiente que vas a
querer hacer es mostrárselo a todos, es decir, llevarlo a producción o hacer el deployment, para ello, los pasos
que tienes que seguir no difieren mucho de cualquier otro proyecto de PHP, independientemente si este proyecto
es un framework o no, pero, en este capítulo veremos algunas consideraciones que puedes tener en cuenta, es
importante recordar que hay muchas formas de hacer lo mismo y los pasos pueden variar dependendiendo de las
opciones brindadas por el hosting que estés empleando.
El servidor por excelencia empleando para publicar o servir un proyecto en Laravel en un hosting es Apache,
Laravel puede ser servida de varias maneras:
https://laravel.com/docs/master/deployment
Pero, si revisamos el mercado actual, la mayoría de los hostings cuentan con al menos las siguientes
características para el plan básico, que es el más económico:
1. PHP
2. MySQL
3. Apache
Lo primero que debes hacer en estos casos es verificar las versiones mínimas, usualmente la más importante es
la de PHP que para la versión actual del framework es la de 8.2 o superior, si tu hosting no cuenta con la versión
mínima de PHP, no podrás emplear la versión 11 o superior, en estos casos, puedes emplear una versión inferior
del framework alguna que sea soportara por el hosting y esta es la primera recomendación que debes de tener
en cuenta. De igual manera, si tienes dudas, puedes consultar al hosting que deseas contratar si tiene soporte
para Laravel.
Una de las grandes ventajas y desventajas de Laravel es que las últimas versiones usualmente emplean
versiones modernas de PHP, esto es bueno ya que podemos emplear todas las características modernas y
correcciones a fallas de seguridad, pero también es una desventaja ya que muchas veces no es posible emplear
las últimas versiones de PHP o simplemente el hosting no ha actualizado a estas últimas versiones.
Quedando claro el aspecto principal que tenemos que tener en cuenta al momento de publicar nuestro proyecto,
vamos a tratar otro punto importante, Node.
Integración con Node
Como se ha venido comentando, una de las diferencias fundamental que tenemos en Laravel con respecto a
otros frameworks, es que tenemos una integración con Node de manera directa, podemos agregar tecnologías
del lado del cliente (o del servidor si lo requieres) fácilmente con Node, instalar React, Vue o Angular para
desarrollar nuestras aplicaciones; por ejemplo, supone que también tenemos una integración con Vue, como
enseñamos en el libro, antes de publicar, recuerda también ejecutar el comando para generar los archivos del
cliente a producción, que usualmente es el de:
$ npm run build
662
Y automáticamente, debes de probar el desarrollo realizado en Node, que debería de funcionar de igual manera
que cuando estabas en ambiente de desarrollo:
$ npm run dev
Si la aplicación fue desarrollada correctamente, en caso de que tengas un error, debes de corregirlo y ejecutar el
comando de:
$ npm run build
Nuevamente.
Los comandos empleados para generar los builds los puedes consultar desde:
package.json
Aunque usualmente con los dos anteriores es suficiente.
Ya hemos empleado el comando de dev que es el empleado al momento del desarrollo, para producción debes
de usar el de build; con esto, ya se generarán los archivos de salida en la carpeta de public.
Archivos y carpetas a publicar
El siguiente punto que tenemos que tener en cuenta es que vamos a llevar a producción, debemos de publicar
todo el proyecto salvo la carpeta de node_modules.
Con especial interés a la carpeta vendor, que es donde se encuentran las librerías y paquetes que conforman el
proyecto incluyendo el mismo framework de Laravel que tiene a ser bastante grande, la carpeta de los módulos
de node solo se emplea en fase de desarrollo.
Simplifica o descarta el archivo .env
Como comentamos anteriormente evita en la mayoría de lo posible emplear las variables de entorno en el .env,
en caso de que sea imprescindible para ti, puedes emplearlo pero, simplifica el mismo lo más que puedas.
Subir el proyecto al hosting
Ya en este punto estamos dando por hecho de que tienes un hosting contratado, en el panel de administración de
los hostings, existe un apartado para manejar las conexiones FTPs, las cuales nos permitirán subir cada una de
las carpetas y archivos del proyecto según lo listado anteriormente, así que, debes de crearte una cuenta desde
el Cpanel de tu hosting.
Y con estos datos, puedes emplear un cliente FTP como FileZilla, en el cual, en el apartado de:
Archivos - Gestor de sitios
663
Puedes colocar las credenciales especificadas por tu hosting y usuario; con esto, es posible realizar una
conexión y subir todos los archivos del proyecto, usualmente debes de subirlo en la carpeta de public_html o
www, seguramente tendrás varias carpetas en el hosting, pero, debe haber alguna de ella en la cual te señalan
con un archivo como el siguiente:
/domains/<YourDomain>/DO_NOT_UPLOAD_HERE
/domains/<YourDomain>/public_html
Donde puedes realizar la carga de los archivos y donde no, usualmente debes de arrojar todo el contenido del
proyecto en la raíz de este directorio, y esto es, para que, desde el dominio configurado al servidor, tengas
acceso directo sin estar concatenando la carpeta o carpetas en la URL del dominio; por ejemplo:
/domains/<YourDomain>/public_html/laravelproject
YourDomain.com/laravelproject
Hay algunos servidores como iPage en los cuales puedes colocar un pointer para poder colocar la carpeta raíz
del proyecto en el dominio, y de esta forma es más fácil organizar varios proyectos en un mismo servidor ya que,
puedes apuntar el dominio no a la raíz del servidor si no a una carpeta específica dentro de este.
Aquí puedes ver cómo queda el proyecto al subir el mismo en hostinger:
664
Figura 22-1: Archivos del proyecto en FileZilla
Terminar configuración del proyecto
En este punto, debes de tener en el hosting todos los archivos y carpetas del proyecto, como recomendación,
mantén activo el ambiente de desarrollo para ver los errores del proyecto tal cual lo tenemos hasta este punto:
.env
APP_ENV=local
APP_DEBUG=true
Ya que, con esto podremos verificar rápidamente cuando sucede un error, también, recuerda que cuando tienes
el proyecto en producción, Laravel genera un log de errores en:
storage/logs/laravel.log
Como recomendación adicional, intenta mantener todas las configuraciones posibles en sus correspondientes
archivos y prescindir del .env que si bien es cierto que se puede emplear tanto en ambiente de desarrollo como
de producción, al ser más propenso a errores o de eliminar o cambiar alguna clave valor por error o de
simplemente ser eliminado por error, esto puede traer consecuencias fatales para tu aplicación y fallar de manera
catastrófica; al manejar las configuraciones del en sus archivos correspondientes, hacen que la aplicación sea
más segura para evitar este tipo de situaciones.
Configurar la base de datos
Desde tu hosting, debes de crear una base de datos y ejecutar la de tu proyecto que, al ser una aplicación a
producción, debes de decidir qué registros eliminas y cuales mantienes para producción; el proceso comienza
exportando la base de datos desde tu proyecto, para esto, puedes emplear cualquier cliente como los que
empleamos en este libro como lo son HeidiSQL o TablePlus y lo exportas en formato SQL.
Luego, con esta base de datos exportada, el siguiente paso es crear una base de datos vacía en el hosting para
la posterior ejecución del SQL anteriormente generado, al momento de crear la base de datos, recuerda colocar
los datos de conexión en el proyecto en producción:
config\database.php
'mysql' => [
'driver' => 'mysql',
'url' => env('DB_URL'),
'host' => env('DB_HOST', '127.0.0.1'),
'port' => env('DB_PORT', '3306'),
'database' => env('DB_DATABASE', '<YourDB>'),
'username' => env('DB_USERNAME', '<YourUsername>'),
'password' => env('DB_PASSWORD', '<YourPassword>'),
665
.htaccess
Posiblemente, si intentas ingresar a la aplicación desde el dominio en producción, veras un error de 403 o similar,
esto es debido a que los únicos archivos públicos se encuentran dentro de la carpeta public, que se encuentra en
la raíz del proyecto, necesitamos un mecanismo para decirle a Apache que el archivo de arranque del framework,
el index.php se encuentra dentro de la carpeta public y no en la raíz, para ello, definimos el siguiente contenido
dentro del .htaccess:
.htaccess
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
Con esta configuración lista, ya debería de funcionar la aplicación con normalidad.
Conclusión
Los pasos mostrados en este capítulo son de carácter informativo y pasa ser tomadas como referencia para
cuando quieres servir tu proyecto a producción, sin embargo, dependiendo del proyecto y el hosting que vayas a
emplear, puede que tengas que emplear más configuraciones o variar las aquí especificadas.
Donde seguir desde aquí:
Sin mucho más que decir, espero que este libro fuese más de lo que esperas del mismo; si te quedaron algunas
dudas, recuerda que puedes releer parte o todo el libro para afianzar tus ideas.
Recuerda que, para poder dominar este framework, tienes que realizar varios proyectos, plasmar tus propias
ideas en tus proyectos, y modificar las presentadas en este libro.
Si te quedaste con ganas de más, en mi canal de YouTube cuento con más recursos, aparte de que cuento con
un curso de Laravel que cuenta con 60 horas, en la cual vemos lo presentado en este libro y mucho más; este
curso lo puedes conseguir desde mi blog en desarrollolibre.net/cursos el cual se encuentra disponible en
desarrollolibre.net/academia y en otras plataformas.
Esto no será lo último de este libro, ya que, lo pienso mantener por mucho tiempo; a medida que vaya recibiendo
retroalimentación de ustedes, los lectores, iré haciendo correcciones y agregando nuevos capítulos; recuerda que
el mantenimiento de este libro depende de ti. Que por favor compartas el enlace de donde comprar este libro, por
tus redes sociales para que más personas puedan interesarse en este escrito y con esto, mientras a más
personas llegue el libro, más contenido iré aportando.
El libro está actualmente en desarrollo.
666
Para solucionar el error, accede a la sesion desde la funcion de ayuda y no la peticion:
session()
session()->regenerate()
Por defecto una Rest Api es sin estado, lo que significa que NO mantiene sesion, Sanctum hace para la
autenticacion SPA un hibrido con la sesión y las cookies, por eso es que vez a lo largo de la seccion que indico
que en lo personal no me gusta mucho esta forma de autenticacion, aunque es completamente valida, el
problema es ese, y ya esto es a juicio personal ya que la doc oficial de Laravel es excelente pero en mi opinion la
parte de Sanctum con la SPA es complicada de seguir y faltan ejemplos, la auth SPA cambio a partir de la version
11 al momento de simplificar la estructura y no hay muchas info al respecto, el request aqui fijate que es un:
ParameterBag
Ya que la peticion es desde la rest api a diferencia de lo que obtendriamos desde el módulo web:
Illuminate\Http\Request
Logicamente, no es lo mismo y desde la API no se puede acceder a la sesion mediante el request, asi que,
cambia el esquema.
Me comentas cualquier cosa.
Dejo el código completo para quien le sirve para la SPA, el mismo está en el repo en github al final de la sección.
function login(Request $request)
{
$validator = validator()->make(
$request->all(),
[
'email' => 'required',
'password' => 'required'
]
);
if ($validator->fails()) {
return response()->json($validator->errors(), 422);
}
$credentials = $validator->valid();
if (auth()->attempt($credentials)) {
// $token = auth()->user()->createToken('myapptoken')->plainTextToken;
// session()->put('token', $token);
return response()->json(
[
'isLoggedIn' => true,
// 'token' => $token,
'user' => auth()->user(),
]
);
}
667
return response()->json('The username and/or password do not match', 422);
}
668
