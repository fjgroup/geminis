![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.001.jpeg)

<a name="_page1_x28.35_y28.35"></a>**Primeros pasos con Vue 3**

Aquí continúa tu camino en el desarrollo de aplicaciones web con Vue ***Andrés Cruz Yoris***

Esta versión se publicó: 2024-02-01

<a name="_page2_x28.35_y28.35"></a>**¡Postea sobre el libro!**

Por favor ayuda a promocionar este libro.

El post sugerido para este libro es:

¡Acabo de comprar el libro "Primeros pasos con Vue 3" de @LibreDesarrollo!

Hazte con tu copia en: <https://www.desarrollolibre.net/libros/primeros-pasos-con-vue>

<a name="_page3_x28.35_y28.35"></a>**Sobre el autor**

Este libro fue elaborado por Andrés Cruz Yoris, Licenciado en Computación, con más de 10 años de experiencia en el desarrollo de aplicaciones web en general; trabajo con PHP, Python y tecnologías del lado del cliente como HTML, JavaScript, CSS, Vue entre otras y del lado del servidor como Laravel, Flask, Django y CodeIgniter. También soy desarrollador en Android Studio, xCode y Flutter para la creación de aplicaciones nativas para Android e IOS.

Pongo a tú disposición parte de mi aprendizaje, reflejado en cada una de las palabras que componen este libro, mi dieciseisavo libro en el desarrollo de software, pero el primero enfocado exclusivamente en JavaScript, para el desarrollo de aplicaciones web con Vue en su versión 3.

<a name="_page4_x28.35_y28.35"></a>**Copyright**

Ninguna parte de este libro puede ser reproducido o transmitido de ninguna forma; es decir, de manera electrónica o por fotocopias sin permiso del autor.

**Prólogo**

Vue es un framework versátil empleado en la creación de sitios web de tipo SPA; es una tecnología modular, basada en componentes donde un componente puede verse como una pequeña pieza de código y podemos agrupar componentes para crear componentes más complejos.

Vue es un framework pequeño, simple y liviano si lo comparamos con otros frameworks como React o Angular, pero su simpleza nos da ventanas como:

- Curva de aprendizaje menos elevada que la de su competencia.
- El framework es de un menor tamaño que el de la competencia (unos 470 KB y 18 KB minificados).
- Es un framework versátil lo que significa que puede ser empleado junto con otras soluciones como typescript.
- Es un framework reactivo, lo que significa que cuando se actualiza su modelo de datos se actualiza la vista y viceversa.
- Vue es un framework progresivo, lo que significa que podemos extenderlo mediante otros plugins como Vuex, Router, Testing entre otros soportados de manera oficial.

Este libro es mayoritariamente práctico, iremos conociendo los fundamentos de Vue, conociendo sus características principales en base a una pequeña aplicación que iremos expandiendo capitulo tras capitulo.

**Para quién es este libro**

Este libro está dirigido a cualquier persona que quiera aprender a desarrollar en Vue 3 sus primeras aplicaciones y conocer uno de los frameworks más famosos en el desarrollo de aplicaciones web del lado del cliente.

Para aquellas personas que conozcan cómo programar en Vue u otros frameworks web del lado del cliente. Para aquellas personas que quieran aprender algo nuevo.

Para las personas que quieran mejorar una habilidad y que quieran crecer como desarrollador y que quiera seguir escalando su camino en el desarrollo de aplicaciones con Vue 3.

**Consideraciones**

Recuerda que cuando veas el símbolo de $ es para indicar comandos en la terminal; este símbolo no lo tienes que escribir en tu terminal, es una convención que ayuda a saber que estás ejecutando un comando.

Al final de cada capítulo, tienes el enlace al código fuente para que lo compares con tu código.

El uso de las **negritas** en los párrafos tiene dos funciones:

1. Si colocamos una letra en **negritas** es para resaltar algún código como nombre de variables, tablas o similares.
1. Resaltar partes o ideas importantes.

Para mostrar tips usamos el siguiente diseño: Tips importantes![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.002.png)

Para los fragmentos de código:

import { createApp } from 'vue' import App from './App.vue'![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.003.png)

createApp(App).mount('#app')

Al emplear los \*\*\*

Significa que estamos indicando que en el código existen fragmentos que presentamos anteriormente.

Como recomendación, emplea Visual Studio Code como editor, ya que, es un editor excelente, con muchas opciones de personalización, extensiones, intuitivo, ligero y que puedes desarrollar en un montón de plataformas, tecnologías, frameworks y lenguajes de programación; así que, en general, Visual Studio Code será un gran compañero para ti.

<https://code.visualstudio.com/>

Este libro tiene un enfoque práctico, por lo tanto, iremos presentando los componentes principales de Vue 3, que va desde un hola mundo hasta conocer estructuras más complejas y de esta manera tener un enfoque claro de esta tecnología. Recuerda que puedes consultar desde el índice del libro, los distintos componentes en caso de que quieras revisar algún tema en particular en cualquier momento.

Se recomienda que se lea cada uno de los capítulos en el orden en el cual se encuentran dispuestos ya que, la aplicación que vamos construyendo va evolucionando en cada capítulo y la misma es referenciada en cada uno de los capítulos.

**Fe de errata y comentarios**

Si tienes alguna duda, recomendación o encontraste algún error, puedes hacerla saber en el siguiente enlace: <https://www.desarrollolibre.net/contacto/create>

Por mi correo electrónico:

desarrollolibre.net@gmail.com

O por Discord en el canal de HTML-CSS-JS:

<https://discord.gg/sg85Zgwz96>

Como recomendación, antes de reportar un posible problema, verifica la versión a la que te refieres y lo agregas en tu comentario; la misma se encuentra en la segunda página del libro.

**Introducción**

Esta guía tiene la finalidad de dar los primeros pasos con Vue 3 empleando JavaScript; con esto, vamos a plantear dos cosas:

1. No es un libro que tenga por objetivo conocer al 100% Vue en su versión 3, o de cero a experto, ya que, sería un objetivo demasiado grande para el alcance de esta guía, si no, conocer que nos ofrece y crear las primeras aplicaciones web con Vue, conocer el uso de los componentes, hooks entre otras funcionalidades del framework.
1. Se da por hecho de que el lector tiene conocimientos al menos básicos en JavaScript, HTML y CSS.

Este libro tiene un enfoque práctico, conociendo los aspectos claves de la tecnología y pasando a la práctica, implementando de a poco pequeñas características de una aplicación que tiene alcance real.

Para seguir este libro necesitas tener una computadora con Windows, Linux o MacOS. El libro se encuentra actualmente en desarrollo.

**Mapa**

Este libro consta de 5 capítulos, con los cuales conoceremos en detalle las características más importantes y básicas de Vue en su versión 3:

Capítulo 1: En este capítulo vamos a conocer las características básicas de Vue como sus principales características, modos de instalación y creación de proyectos, realizaremos un hola mundo para presentar las principales características del framework web.

Capítulo 2: En este capítulo vamos a conocer los 3 bloques de Vue, bloque de script, template y estilo, además de crear pequeños ejemplos para ir presentando las principales características de Vue.

Capítulo 3: En este capítulo vamos a crear nuestro primer proyecto tipo CRUD empleando Vue y una Rest Api tipo CRUD existente; es decir, una Api Rest con un alcance limitado junto con Oruga UI como framework web del lado del cliente basado en componentes.

Capítulo 4: En este capítulo vamos a crear otra aplicación en Vue tipo CRUD empleando Naive UI en lugar de Oruga UI como framework web basado en componentes.

Capítulo 5: En este capítulo vamos a crear una aplicación con Pinia y aprender a emplear este manejador de estado y entender sus componentes que son el store, state, actions y getters.

<a name="_page11_x28.35_y28.35"></a>Tabla de Contenido

[**Primeros pasos con Vue 3 1** ](#_page1_x28.35_y28.35)[¡Postea sobre el libro! 3** ](#_page2_x28.35_y28.35)[Sobre el autor 4** ](#_page3_x28.35_y28.35)[Copyright 5** ](#_page4_x28.35_y28.35)[Tabla de Contenido 12 ](#_page11_x28.35_y28.35)[**Capítulo 1: Introducción a Vue 1** ](#_page14_x28.35_y50.55)[Sobre Vue 1 ](#_page14_x28.35_y153.31)[Componentes en Vue 1 ](#_page14_x28.35_y591.22)[Formas de instalar/usar Vue 2 ](#_page15_x28.35_y202.90)[Usando Vue mediante la CDN 3 ](#_page16_x28.35_y394.81)[Creando un proyecto en Vue mediante Node 3](#_page16_x28.35_y520.86)

[Crear un proyecto en Vue mediante Vue CLI 4 ](#_page17_x28.35_y276.96)[Estructura del proyecto 5](#_page18_x28.35_y642.74)

[Vue en la práctica, app de contador 11 ](#_page24_x28.35_y28.35)[V-model 13 ](#_page26_x28.35_y481.14)[Formulario 14 ](#_page27_x28.35_y336.21)[**Capítulo 2: Bloques de script, template y style en Vue 16** ](#_page29_x28.35_y61.47)[Características principales de Vue - Opciones principales en el bloque de script 16 ](#_page29_x28.35_y183.39)[Propiedad data, variables reactivas 16 ](#_page29_x28.35_y371.89)[Method 17 ](#_page30_x28.35_y263.48)[Propiedades computadas 17](#_page30_x28.35_y566.48)

[Props 20 ](#_page33_x28.35_y42.89)[Ciclo de vida de los componentes 22 ](#_page35_x28.35_y573.73)[Instalar plugins 23 ](#_page36_x28.35_y292.30)[Vue Router 23](#_page36_x28.35_y550.61)

[Axios 24 ](#_page37_x28.35_y101.08)[Compartir datos entre componentes 24 ](#_page37_x28.35_y353.14)[Reactividad en los arrays 24 ](#_page37_x28.35_y557.66)[Ref() 27 ](#_page40_x28.35_y516.61)[Símbolo de @ para importar componentes o archivos 27 ](#_page40_x28.35_y598.76)[Composition API 28 ](#_page41_x28.35_y28.35)[Option API 28 ](#_page41_x28.35_y65.41)[Opciones principales en el bloque se template 28 ](#_page41_x28.35_y118.46)[Impresiones 28 ](#_page41_x28.35_y192.72)[Condicionales 28 ](#_page41_x28.35_y289.68)[v-if 28 ](#_page41_x28.35_y400.93)[V-show 30](#_page43_x28.35_y72.25)

[v-for 30 ](#_page43_x28.35_y325.11)[v-for con un objeto 31 ](#_page44_x28.35_y630.59)[V-model 33 ](#_page46_x28.35_y471.11)[Eventos 36 ](#_page49_x28.35_y28.35)[Alias Key 37](#_page50_x28.35_y145.25)

[Teclas modificadoras del sistema 37 ](#_page50_x28.35_y329.22)[Eventos personalizados 37](#_page50_x28.35_y513.46)

[Opciones principales en el bloque de style 39 ](#_page52_x28.35_y57.44)[Conclusiones 39 ](#_page52_x28.35_y337.99)[**Capítulo 3: Consumir una Rest Api tipo CRUD con Vue y Oruga UI 40** ](#_page53_x28.35_y61.47)[Api CRUDCRUD primeros pasos 40 ](#_page53_x28.35_y158.78)[Crear el proyecto 42 ](#_page55_x28.35_y101.34)[Configurar proyecto con CRUDCRUD 43 ](#_page56_x28.35_y162.17)[Configurar proyecto en Vue 3 con Oruga UI 43 ](#_page56_x28.35_y618.34)[Generar un listado 46 ](#_page59_x28.35_y101.08)[Configurar axios 46 ](#_page59_x28.35_y336.66)[Consumir Api mediante axios 47 ](#_page60_x28.35_y454.44)[Instalar Material Design Icons 49 ](#_page62_x28.35_y595.77)[Demo: Paginación 50 ](#_page63_x28.35_y410.80)[Ruteo con Vue Router 53 ](#_page66_x28.35_y538.43)[Instalación 53](#_page66_x28.35_y641.77)

[Definir rutas 54 ](#_page67_x28.35_y28.35)[Componente para el renderizado de los componentes 55 ](#_page68_x28.35_y28.35)[Establecer las rutas 55](#_page68_x28.35_y227.01)

[Crear enlaces 55 ](#_page68_x28.35_y397.23)[Componente para crear y editar post 56 ](#_page69_x28.35_y160.59)[Obtener las categorías 58](#_page71_x28.35_y559.67)

[Demo: Crear un post con validaciones 59 ](#_page72_x28.35_y396.25)[Editar un registro 63](#_page76_x28.35_y28.35)

[Eliminar un registro 65 ](#_page78_x28.35_y87.59)[Tailwind.css en el proyecto en Vue con Oruga UI 66 ](#_page79_x28.35_y28.35)[Container 67 ](#_page80_x28.35_y366.10)[Cambios varios en el componente de listado 67 ](#_page80_x28.35_y551.40)[Cambios varios en el componente de guardado 69 ](#_page82_x28.35_y28.34)[Mensaje de confirmación para eliminar 70 ](#_page83_x28.35_y312.17)[Mensaje de acción realizada 72 ](#_page85_x28.35_y211.80)[Demo: Upload de archivos 74 ](#_page87_x28.35_y161.13)[Recurso Rest 74](#_page87_x28.35_y293.56)

[Vue 3 y componente upload en Oruga UI 75 ](#_page88_x28.35_y389.27)[Manejo de errores de formulario 77 ](#_page90_x28.35_y145.52)[Opcional: Upload de archivos vía Drag and Drop 78 ](#_page91_x28.35_y337.27)[**Capítulo 4: Consumir una Api Rest tipo CRUD con Vue y Naive UI 81** ](#_page94_x28.35_y61.47)[Preparar el entorno 81 ](#_page94_x28.35_y437.55)[Vue Router 81 ](#_page94_x28.35_y497.26)[Instalación 81 ](#_page94_x28.35_y586.05)[Configuración del proyecto 81 ](#_page94_x28.35_y668.47)[Creación de componentes 83 ](#_page96_x28.35_y28.34)[Configurar axios 83 ](#_page96_x28.35_y265.55)[Fase 1: Listados 83 ](#_page96_x28.35_y623.85)[Consumir la Rest Api mediante axios (primeras pruebas) 84](#_page97_x28.35_y28.34)

[Naive UI, para los componentes de interfaz gráfica 85 ](#_page98_x28.35_y596.53)[Instalar 86 ](#_page99_x28.35_y28.34)[Configurar 86 ](#_page99_x28.35_y109.58)[Configurar tabla en los listados 86 ](#_page99_x28.35_y265.25)[Container 87](#_page100_x28.35_y246.02)

[Layout 87 ](#_page100_x28.35_y428.69)[Menú 89](#_page102_x28.35_y537.79)

[Menú: Navegación mediante Router Link 91 ](#_page104_x28.35_y282.92)[Menú: Enlaces dinámicos 92](#_page105_x28.35_y646.20)

[Menú: Reutilizable 94 ](#_page107_x28.35_y590.41)[Header con menú 96](#_page109_x28.35_y71.98)

[Instalar y configurar Tailwind CSS 97 ](#_page110_x28.35_y265.67)[Arreglar pequeños detalles 98 ](#_page111_x28.35_y425.08)[Listado de elementos 99](#_page112_x28.35_y189.16)

[Rutas agrupadas 100 ](#_page113_x28.35_y235.71)[Ruta para el listado de elementos desde el layout 101 ](#_page114_x28.35_y391.36)[Detalle del elemento 102](#_page115_x28.35_y101.61)

[Ruta de detalle del elemento 103 ](#_page116_x28.35_y101.08)[Extra: Introducción a sobrescribir el tema de NaiveUI 103 ](#_page116_x28.35_y651.50)[Fase 2: CRUDs y formularios 106 ](#_page119_x28.35_y72.78)[Demo: Crear una categoría con validaciones en el servidor 106](#_page119_x28.35_y147.03)

[Editar una categoría 109 ](#_page122_x28.35_y426.60)[Enlaces para el CRUD 111 ](#_page124_x28.35_y542.51)[Componente de formulario para el tipo 112 ](#_page125_x28.35_y146.84)[Enlaces para el CRUD 114 ](#_page127_x28.35_y443.08)[Componente de formulario para el elemento 115](#_page128_x28.35_y397.31)

[Definir Ruta 121 ](#_page134_x28.35_y86.80)[Enlaces para el CRUD 121 ](#_page134_x28.35_y346.95)[CKEditor: Editor para el contenido enriquecido 122 ](#_page135_x28.35_y609.91)[Integrar Vue con CKEditor 123](#_page136_x28.35_y304.99)

[Habilitar el CSS para CKEditor 125 ](#_page138_x28.35_y378.91)[Opciones de menús navegables 126 ](#_page139_x28.35_y250.52)[Redirecciones en rutas inexistentes 128 ](#_page141_x28.35_y146.84)[Cambios visuales 129 ](#_page142_x28.35_y101.88)[Múltiples router-views 129 ](#_page142_x28.35_y453.56)[**Capítulo 5: Pinia 133** ](#_page146_x28.35_y28.34)[Ejemplo mínimo 134 ](#_page147_x28.35_y325.28)[Conceptos claves 135 ](#_page148_x28.35_y233.84)[Store 135](#_page148_x28.35_y366.30)

[State 136 ](#_page149_x28.35_y28.34)[Getters 136 ](#_page149_x28.35_y79.95)[Actions 136 ](#_page149_x28.35_y133.01)[Aplicación con Vue y Pinia 136](#_page149_x28.35_y258.81)

<a name="_page14_x28.35_y50.55"></a>**Capítulo 1: Introducción a Vue**

En este capítulo, vamos a conocer más sobre la tecnología, su propósito, como funciona, características, formas de instalarlo, composición de archivos y finalmente tener el primer contacto con la tecnología mediante el clásico “Hola Mundo”.

<a name="_page14_x28.35_y153.31"></a>Sobre Vue

Vue es el framework del momento y ha logrado tener un hueco entre los poderosos Angular y React que llevan más tiempo en el mercado, al ser un framework ligero, versátil y con una curva de aprendizaje menor si lo comparamos con la competencia; una de sus principales características es que es muy liviano y fácil de utilizar, además de ser progresivo, lo que significa que al implementar un proyecto entero, podemos decidir si toda la aplicación va a emplear Vue o solamente una parte de ella.

Vue es un framework cuyas funcionalidades están disponibles en otros frameworks como React o Angular:

- Tenemos directiva que manipula el modelo de datos y que luego se vinculan al DOM del documento; y gracias a la reactividad de Vue, a medida que cambian los datos reactivos, otras partes de un componente y otros componentes que hacen referencia a esos valores se actualizan automáticamente. Ésta es la magia de Vue o de este tipo de frameworks en general y es una de las razones por las que podemos crear un sistema completo y que puede parecer complejo con relativa facilidad; en otras palabras, Vue se encarga de observar los cambios que realizamos nosotros mediante formularios o eventos en general y replicar los cambios a lo largo de la aplicación y de esta forma nosotros no tenemo que implementar un comportamiento de este tipo.
- Tenemos el uso de los props, que permiten pasar datos entre componentes.
- Tenemos propiedades computadas, que se derivan de otras propiedades y son empleadas para realizar cálculos complejos u otra lógica del componente; además de que las propiedades computadas solamente se vuelven a evaluar cuando alguna de las dependencias que definen la propiedad computada ha cambiado.
- Tenemos observadores o watches que nos permiten observar cambios de datos reactivos. Los datos reactivos son datos que Vue.js observa y las acciones se realizan automáticamente cuando los datos reactivos cambian.
- También tenemos accesos a bibliotecas populares para extender el funcionamiento base del framework, Vue, a diferencia de otros frameworks, no cuenta con un sistema de ruteo por defecto, en su lugar, debemos de instalar una biblioteca llamada Vue Router, para manejar el enrutamiento; también tenemos bibliotecas para la gestión de estado como Pinia o Vuex.

<a name="_page14_x28.35_y591.22"></a>Componentes en Vue

Los componentes son la pieza fundamental en Vue para construir aplicaciones, una aplicación de Vue consiste en múltiples componentes que los puedes ver cómo piezas de lego que en conjunto forman nuestra aplicación; los componentes son piezas de interfaz gráfica como listados, headers, formularios para crear, actualizar entre otras operaciones.

Vue.js es un framework progresivo para construir interfaces de usuario y es una diferencia fundamental de otros frameworks monolíticos, Vue está diseñado para ser utilizado incrementalmente lo que permite incluir Vue en una

1

parte de la aplicación; estas piezas de código empleada se llaman componentes. Los componentes son bloques de código que se utilizan para definir los elementos de la interfaz de usuario; cada componente se compone de tres secciones o bloques: Script, Template y Style:

1. Script: Este bloque se utiliza para definir la lógica del componente mediante JavaScript; aquí también se declaran las propiedades, props, al igual que los métodos y los hooks y cualquier otra característica soportada por el framework.
1. Template: Este bloque se utiliza para definir el HTML, es decir, la estructura del componente, aquí también se emplea la sintaxis de plantilla que provee Vue.js para, por ejemplo, imprimir variables o similares, condicionales o ciclos.
1. Style: En este bloque se utiliza para definir el estilo de la aplicación a los elementos HTML definidos en el apartado de template; este estilo se puede especificar para que sea global a la aplicación o solamente local al componente.

<a name="_page15_x28.35_y202.90"></a>Formas de instalar/usar Vue

Hay muchas formas de crear un proyecto en Vue, podemos usar la opción de CDN, que es ideal cuando queremos experimentar un poco con el framework o hacer proyectos pequeños o con alcance limitado y mediante la Vue CLI y Node; de esto, hablaremos en los siguientes apartados.

Vue es tan versátil que podemos incluirlo no sólo como parte de una solución, es decir, que gobierne una página completa:

![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.004.png)

Figura 1-1: Aplicación completa en Vue

Si no, podemos incluirlo como parte de una aplicación existente, es decir, que no esté desarrollada en Vue y es por eso que se conoce como progresivo:

![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.005.png)

Figura 1-2: Aplicación en la cual está particularmente implementado Vue

Es decir, que no tenemos que tener una aplicación web definida con Vue en el 100% de la página o la totalidad de la misma; podemos especificar que solamente un contenedor (por ejemplo, un DIV) sea el único que va a controlar Vue.

<a name="_page16_x28.35_y394.81"></a>Usando Vue mediante la CDN

Está opción de instalación es la más simple, pero, la menos escalables ya que perdemos todas las ventajas que tenemos en un proyecto en Node, como su inmensa cantidad de plugins y gestión de los paquetes mediante NPM; pero, es la mejor opción si queremos realizar proyectos con alcance limitados o de pruebas; para ello, agregamos en una página HTML la dependencia de Vue:

<script src="https://unpkg.com/vue@next"></script>![ref1]

<a name="_page16_x28.35_y520.86"></a>Creando un proyecto en Vue mediante Node

Comencemos creando un proyecto en Node mediante: $ npm init![ref1]

Preguntará información base como:

name: (project-name) <project-name> version: <0>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.007.png)

description: <The Project Description> entry point: <//leave empty>

test command: <//leave empty>

git repository: <//the repositories url>

keywords: <//leave empty> author: <// your name> license: <N/A>![ref2]

Nos movemos dentro del nuevo proyecto:

cd <project-name>![ref3]

Y agregamos Vue mediante:

$ npm install vue![ref1]

Con esto, instalamos la última versión del framework en el proyecto que creamos anteriormente. Tienes más información en:

<https://es.vuejs.org/>

<a name="_page17_x28.35_y276.96"></a>Crear un proyecto en Vue mediante Vue CLI

Vue CLI es una herramienta para la línea de comandos que permite crear, configurar y administrar proyectos en Vue; es una solución excelente ya que, al crear un proyecto, crea todos los elementos necesarios con la estructura típica de un proyecto en Vue, aparte de que el asistente pregunta por otros complementos que podemos agregar como Vuex o Vue Router; comenzamos instalando Vue CLI de manera global, es decir, a nivel del Sistema Operativo:

$ npm install -g @vue/cli![ref1]

O si usas yarn:

$ yarn global add @vue/cli![ref1]

Tienes más información en:

<https://cli.vuejs.org/>

Una vez instalado, el siguiente paso es crear el proyecto, para ello, ocupamos el siguiente comando: $ vue create hello-world![ref1]

Seleccionamos la opción por defecto que es con Vue en su versión 3:

? Please pick a preset: (Use arrow keys)![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.010.png)

\> Default ([Vue 3] babel, eslint)

Default ([Vue 2] babel, eslint) Manually select features

Una vez terminado el proceso de creación del proyecto tendremos una salida como la siguiente:

Generating README.md...![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.011.png)

Successfully created project hello-world. Get started with the following commands:

$ cd hello-world $ npm run serve

En la cual, como puedes apreciar, se acaba de crear una carpeta con el nombre del proyecto definido, en este ejemplo, "hello-world", debemos de posicionar la terminal mediante el comando cd:

cd hello-world![ref1]

Y servir el proyecto mediante: npm run serve![ref4]

Al ejecutar este comando, veremos una salida como la siguiente:

App running at:![ref5]

- Local: http://localhost:8080/
- Network: http://192.168.1.26:8080/

En la cual, Vue acaba de exponer una URL para acceder a la aplicación desde el navegado en local, que es la que usamos al momento de desarrollar:

http://localhost:8080/

O en la red local: http://192.168.1.26:8080/

Desde este momento, puedes arrastrar la carpeta del proyecto a tu editor, como lo es VSC para empezar a desarrollar. Verás que, al modificar cualquier archivo del proyecto, el servidor recarga automáticamente inyectando los cambios y con esto podemos verlos desde el navegador sin necesidad de reiniciar el servidor de manera manual.

Este es el proyecto que emplearemos a lo largo del capítulo.

Para actualizar Vue en un proyecto existente, usamos el siguiente comando: $ vue upgrade![ref1]

<a name="_page18_x28.35_y642.74"></a>Estructura del proyecto

Vamos a explicar en qué consiste los archivos y carpetas que forman el proyecto:

![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.014.png)

Figura 1-1: Estructura de un proyecto

- En la carpeta **public** se encuentran los archivos estáticos de la aplicación que no serán procesados por el framework
- En la carpeta **src/assets** se se encuentran los archivos estáticos de la aplicación como el Favicon, imágenes, entre otros y que pueden ser referenciados desde la aplicación, por lo tanto, a diferencia de la carpeta anterior, son procesados por el framework.
- En la carpeta de **components** se encuentran definidos los componentes de la aplicación, por defecto al momento de crear la aplicación tenemos uno de ejemplo llamado **HelloWorld.vue**.
- El componente llamado **App.vue** es el componente raíz de la aplicación de Vue en donde se cargan el resto de componentes.
- El archivo **main.js** es el principal de la aplicación y es donde se crea la instancia principal de Vue y carga el componente raíz, el **App.vue**, en este archivo es donde se realizan las configuraciones globales como inicializar plugins.
- El archivo **babel.config** es un archivo de configuración empleado por Babel (una de las dependencias de Vue) que permite transformar el código JavaScript que nosotros usamos para programar la aplicación en Vue con las últimas novedades de JavaScript a un código más antiguo que soportan los navegadores; este proceso se realiza para lanzar la aplicación a producción o en desarrollo.
- Los archivos de **package.json** son para manejar las dependencias del proyecto.
- El archivo de **Vue.config.js** es un archivo de configuración empleado por Vue que permite especificar opciones de compilación.

Ahora, vamos a evaluar el contenido de los archivos principales.

Este es el archivo principal de Vue en donde creamos la aplicación de Vue, es decir, creamos una instancia de la aplicación de Vue; para ello, se emplea la función **createApp()** la cual recibe como parámetro el componente raíz de la aplicación (el llamado App) luego, debemos de especificar mediante la función **mount()** en donde vamos a montar la aplicación; para ello se emplea un **String**; este String es un selector de CSS que indica donde se va a representar la aplicación; desde este archivo se configura la instancia principal de Vue, la cual es la que sirve como punto de entrada a la aplicación, es decir, es lo primero que se carga cuando se ingresa a la aplicación.

src\main.js

import { createApp } from 'vue' import App from './App.vue'![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.015.png)

createApp(App).mount('#app')

El componente anterior llamado **App** en su mayoría consisten en estilos y la definición de una imagen; en este componente se importa y emplea el componente de **HelloWord.vue** que también forma parte del proyecto generado.

src\App.vue

<template>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.016.png)

<img alt="Vue logo" src="./assets/logo.png">

<HelloWorld msg="Welcome to Your Vue.js App"/> </template>

<script>

import HelloWorld from './components/HelloWorld.vue'

export default { name: 'App', components: { HelloWorld

}

}

</script>

<style>

#app {

font-family: Avenir, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;

text-align: center;

color: #2c3e50;

margin-top: 60px; }![ref6]

</style>

El componente de **HelloWorld.vue** consiste en HTML, CSS y JavaScript sin emplear alguna característica importante de Vue excepto su estructura:

src\components\HelloWorld.vue

<template>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.018.png)

<div class="hello">

<h1>{{ msg }}</h1>

<p>

For a guide and recipes on how to configure / customize this project,<br>

check out the

<a href="https://cli.vuejs.org" target="\_blank" rel="noopener">vue-cli documentation</a>.

</p>

<h3>Installed CLI Plugins</h3>

<ul>

<li><a href="https://github.com/vuejs/vue-cli/tree/dev/packages/%40vue/cli-plugin-babel" target="\_blank" rel="noopener">babel</a></li>

<li><a href="https://github.com/vuejs/vue-cli/tree/dev/packages/%40vue/cli-plugin-eslint" target="\_blank" rel="noopener">eslint</a></li>

</ul>

<h3>Essential Links</h3>

<ul>

<li><a href="https://vuejs.org" target="\_blank" rel="noopener">Core Docs</a></li>

<li><a href="https://forum.vuejs.org" target="\_blank" rel="noopener">Forum</a></li>

<li><a href="https://chat.vuejs.org" target="\_blank" rel="noopener">Community Chat</a></li>

<li><a href="https://twitter.com/vuejs" target="\_blank" rel="noopener">Twitter</a></li>

<li><a href="https://news.vuejs.org" target="\_blank" rel="noopener">News</a></li> </ul>

<h3>Ecosystem</h3>

<ul>

<li><a href="https://router.vuejs.org" target="\_blank" rel="noopener">vue-router</a></li>

<li><a href="https://vuex.vuejs.org" target="\_blank" rel="noopener">vuex</a></li>

<li><a href="https://github.com/vuejs/vue-devtools#vue-devtools" target="\_blank" rel="noopener">vue-devtools</a></li>

<li><a href="https://vue-loader.vuejs.org" target="\_blank" rel="noopener">vue-loader</a></li>

<li><a href="https://github.com/vuejs/awesome-vue" target="\_blank" rel="noopener">awesome-vue</a></li>

</ul> </div> </template>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.019.png)

<script>

export default {

name: 'HelloWorld', props: {

msg: String

}

}

</script>

<!-- Add "scoped" attribute to limit CSS to this component only --> <style scoped>

h3 {

margin: 40px 0 0;

}

ul {

list-style-type: none;

padding: 0;

}

li {

display: inline-block;

margin: 0 10px;

}

a {

color: #42b983;

}

</style>

Este archivo es común en cualquier proyecto de Node y encontramos información del proyecto como el nombre y la versión, comandos disponibles:

1. Serve para iniciar un servidor de desarrollo.
1. Build para generar un bundle con los archivos del proyecto que podemos emplear en producción.
1. Lint para detectar errores en el código fuente de las aplicaciones.

Este es un archivo clave en un proyecto en Node en donde se encuentran datos del proyecto como nombre, versión y las dependencias del proyecto, además de los comandos que se pueden ejecutar en el proyecto:

package.json {![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.020.png)

"name": "hello-world",

"version": "0.1.0",

"private": true,

"scripts": {

"serve": "vue-cli-service serve",

"build": "vue-cli-service build", "lint": "vue-cli-service lint"![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.021.png)

},

"dependencies": {

"core-js": "^3.8.3",

"vue": "^3.2.13"

},

"devDependencies": {

"@babel/core": "^7.12.16", "@babel/eslint-parser": "^7.12.16", "@vue/cli-plugin-babel": "~5.0.0", "@vue/cli-plugin-eslint": "~5.0.0", "@vue/cli-service": "~5.0.0",

"eslint": "^7.32.0", "eslint-plugin-vue": "^8.0.3"

}, \*\*\*

}

Hasta ahora hemos visto algunos archivos de configuración y componentes básicos; pero, no hemos visto en donde se va a montar la aplicación, recordemos que Vue es una tecnología web que funciona del lado del cliente y por lo tanto debe de representarse en una página web; al crear la instancia de Vue, mediante un selector indicamos donde se debe mostrar la aplicación:

src\main.js createApp(App).mount('#app')![ref7]

Por lo tanto, desde la página HTML por defecto, definimos un elemento HTML con el identificador anterior, que es donde se va a mostrar la aplicación:

public\index.html

<!DOCTYPE html>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.023.png)

<html lang="">

\*\*\*

<body>

\*\*\*

**<div id="app"></div>** </body>

</html>

Estos archivos son los más importantes de un proyecto en Vue, cómo has podido darte cuenta, es una aplicación de entrada que nos ofrece una estructura básica en la cual montar la aplicación que vamos a desarrollar.

<a name="_page24_x28.35_y28.35"></a>Vue en la práctica, app de contador

Partiendo del proyecto que creamos en el apartado anterior, ahora que conocemos las características básicas de Vue de manera teórica, vamos a conocerlo en la práctica creando nuestra primera aplicación en Vue que será un contador, cuando presionemos un botón un contador aumenta en una unidad; para ello, agregamos el siguiente código:

src\components\CounterComponent.vue

<template>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.024.png)

<div>

{{ counter }}

<button @click="counter++">+1</button>

</div> </template>

<script>

export default { data: function(){

return { counter:0

}

}

}

</script>

Y cargamos desde el componente principal que es el App.vue: src\App.vue

<template>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.025.png)

<Counter/> </template>

<script>

import Counter from './components/CounterComponent.vue'

export default { name: 'App', components: { Counter

// HelloWorld }

}

</script>

Como puedes apreciar, para emplear un componente debemos de importarlo: import Counter from './components/CounterComponent.vue'![ref1]

11

Registrarlo a nivel del componente mediante la opción de **components**:

components: {![ref5]

Counter

}

Y luego, lo podemos emplear desde el template: <Counter/>![ref8]

Volviendo al componente que creamos llamado **Counter.vue**, comenzamos definiendo las variables reactivas que vamos a usar en el proyecto para el contador:

counter:0![ref1]

Que no es más que una variable que inicializamos en cero que será el contador que vamos a emplear para sumar en una unidad al momento de que demos un click sobre el botón.

Desde el template, empleamos un evento click al presionar un botón, incrementamos en una unidad: @click="counter++"![ref1]

En este ejemplo, usamos el JavaScript en línea para incrementar una propiedad definida en el bloque de data: counter++![ref7]

Aunque podemos usar un enfoque más tradicional organizado en funciones: src\components\CounterComponent.vue

<template>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.027.png)

<div>

{{ counter }}

<!-- <button @click="counter++">+1</button> --> <button @click="addOne">+1</button>

</div>

</template>

<script>

export default {

data: function(){

return {

counter:0

}

},

methods: {

addOne(){

this.counter++

} },![ref9]

} </script>

Como puedes apreciar, la estructura es muy similar a la que empleamos en vanilla JavaScript: <button onclick="addOne">+1</button>![ref1]

Aunque, al ser una operación sencilla que podemos realizar en una sola línea, podemos emplear el JavaScript en línea; es importante notar que usamos:

this.counter![ref1]

Para referenciar la variable en línea; para referenciar en el bloque de script cualquier variable, prop o método, usamos la palabra reservada **this** como si se tratara de una clase.

Luego, referenciamos la variable reactiva desde el template: {{ counter }}![ref3]

En resumen, puedes ver un ejemplo mínimo en la cual empleamos una variable reactiva (la declarada en el bloque de **data()**) y al actualizarla en el bloque de script, se actualiza automáticamente en el bloque del template y esto es precisamente el concepto de la reactividad en Vue, pero, viéndolo en la práctica. Todo este comportamiento se realiza sin necesidad de hacer la operación de manera manual de referenciar un elemento mediante el ID para actualizar su valor.

En este sencillo ejemplo podemos apreciar varias características importantes de Vue, como lo son la declaración de propiedades para luego emplearlas en el template, el uso de los eventos y la reactividad de Vue que se emplea al momento de actualizar la propiedad llamada **count**, cuando esto ocurre, todas las partes en donde se encuentre referenciada a nivel del componente son actualizadas, como en este caso es en el template.

<a name="_page26_x28.35_y481.14"></a>V-model

Si en el template, colocamos un **v-model** con el cual podemos referenciar crear una vinculación bidireccional entre los datos y un campo de formulario (TEXTAREA, INPUTS, SELECT, RADIUS... en pocas palabras, podemos usar los **v-model** en todos los tipos de campos de formularios) como un input de tipo texto:

<template>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.029.png)

<input type="text" id="message" v-model="message"> <p>{{ message }}</p>

</template>

<script>

export default {

data() {

return {

message: ''

}

}

} </script>![ref10]

Como puedes ver en el ejemplo anterior, un **v-model** es una variable reactiva que asignamos a un campo de formulario mediante la directiva **v-model**. Si cambiamos el valor en el input de tipo texto, automáticamente se cambia en la variable reactiva y viceversa.

Siguiendo nuestro ejemplo: src\components\CounterComponent.vue

<template>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.031.png)

<div>

{{ counter }}

<input type="number" v-model="counter"> <button @click="addOne">+1</button>

</div> </template>

Veremos que cuando colocamos un valor en el input que usa la directiva **v-model** con **counter**, se actualiza automáticamente el valor de **counter** en toda la aplicación.

<a name="_page27_x28.35_y336.21"></a>Formulario

Empleando el atributo **@submit.prevent** el cual es realmente un escuchador del evento submit del formulario, capturamos el evento y es procesado mediante el método **mySubmit()**:

src\components\CounterComponent.vue

<template>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.032.png)

<div>

{{ counter }}

<form @submit.prevent="mySubmit">

<input type="number" id="message" v-model="counter"> <button type="submit">Send</button>

</form>

</div>

</template>

<script>

export default {

data: function(){

return {

counter:0

}

},

methods: {

mySubmit(){

this.counter++

} },![ref9]

} </script>

Pudiéramos simplificar la operación definiendola en una sola línea: src\components\CounterComponent.vue

<template>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.021.png)

<div>

{{ counter }}

<form @submit.prevent="counter++">

<input type="number" id="message" v-model="counter"> <button type="submit">Send</button>

</form>

</div>

</template>

<script>

export default {

data: function(){

return {

counter:0

}

},

}

</script>

Con estos sencillos ejemplos, podemos entender de una manera práctica la principal característica que tiene este tipo de frameworks que es precisamente la reactividad del framework, que consisten en estas variables especiales, en este ejemplo la llamada **counter** que cuando se actualiza la misma desde el template se actualiza también en el bloque de script y viceversa.

**Capítulo 2: Bloques de script, template y style en <a name="_page29_x28.35_y61.47"></a>Vue**

En este apartado, aprenderemos a emplear las opciones principales que tenemos disponibles para los tipos de bloques en Vue, específicamente conocer en detalle qué herramientas tenemos disponibles al implementar los bloques de script, template y style.

Características principales de Vue - Opciones principales en el bloque de <a name="_page29_x28.35_y183.39"></a>script

En este apartado vamos a conocer las características principales del framework con las cuales creamos los componentes en Vue; en el apartado anterior, vimos cómo crear un sencillo ejemplo en la cual vimos los aspectos más básicos del framework, en este apartado, profundizaremos más en los conceptos que vimos antes e introduciremos algunos nuevos; específicamente, todas las opciones que veremos en este apartado son implementados en el apartado de script:

<script>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.033.png)

export default {

\*\*\*

}

</script>

<a name="_page29_x28.35_y371.89"></a>Propiedad data, variables reactivas

La opción de **data** retorna el estado inicial del componente, es decir, un conjunto de variables reactivas que podemos emplear a lo largo del componente, ya sea en la sección de script o en el template.

La opción de **data** es la más importante es un componente en Vue, al menos la más empleada ya que es el corazón de un componente, en la misma, mediante la definición de una función retorna un objeto que contiene las variables reactivas que se pueden emplear en el componente, específicamente, desde el template imprimiendo las mismas con la sintaxis doble llave {{}}:

<script>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.034.png)

export default { data: function(){

return { counter:0

}

},

}

</script>

Esta opción es una función que retorna un objeto que contiene los datos que se utilizan en el componente y se puede acceder a ellos desde cualquier lugar del componente. Por ejemplo, si desea definir un objeto de datos llamado **message**, puede hacerlo de la siguiente manera:

<script>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.035.png)

export default {

data: function(){ return {

message: "" }

},

}

</script>

En este ejemplo, hemos definido un componente con un objeto que contiene una variable llamada **message**. La variable **message** se utiliza en la plantilla del componente para mostrar un mensaje. Anteriormente vimos un ejemplo en el cual, al darle click a un botón, se actualiza la variable reactiva llamada **counter**.

En este ejemplo, se define un objeto de datos llamado **message** que contiene una cadena de texto. Este objeto se puede utilizar en cualquier lugar del componente, como en la template o en los métodos.

<a name="_page30_x28.35_y263.48"></a>Method

La sección **methods** de una aplicación Vue es donde se definen los métodos que se utilizan en la instancia de Vue; la utilidad de los métodos son muchas, usarlas al inicio de la creación de un componente para inicializar el componente o como respuestas a eventos, por ejemplo, al dar un click sobre un botón, evento submit de un formulario, entre otros.

A la final la sección de methods como indica su nombre podemos definir múltiples métodos, tantos como necesite el componente:

<script>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.036.png)

export default {

methods: {

showAlert: function () {

alert('¡Hello world!') }

}

} </script>

<a name="_page30_x28.35_y566.48"></a>Propiedades computadas

Las propiedades computadas son una característica bastante importante de Vue y nos permiten crear otras propiedades que se calculan a partir de otras propiedades o las variables reactivas en en caso de Vue; de esta forma, podemos evaluar condiciones complejas de manera eficiente y conservando la reactividad de Vue:

- Es eficiente ya que la función solamente se va a reevaluar cuando alguno de los valores que comprende la misma cambia (sus dependencias) y podemos reutilizarla las veces que sean necesarias, las propiedades computadas se almacenan en caché lo que significa que el resultado previamente calculado sin tener que ejecutar la función de nuevo.
- Facilitan la lectura del código: Las propiedades computadas permiten separar la lógica compleja de la plantilla, lo que hace que el código sea más fácil de leer y mantener.
- Se mantiene la reactividad de Vue ya que al cambiar alguno de los valores que comprende la propiedad computada, se actualiza automáticamente.

Veamos un ejemplo: src\components\ComputedComponent.vue

<template>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.037.png)

<ul>

<li> {{ reversedMessage }} </li>

<li> {{ reversedMessage }} </li>

<li> {{ reversedMessage }} </li>

</ul>

</template>

<script>

export default {

data: function(){

return {

message: 'Hello world!'

}

},

methods: {

reversedMessageByMethod(){

console.log('reversedMessageByMethod')

return this.message.split('').reverse().join('') }

},

computed: {

reversedMessage: function () {

console.log('reversedMessage')

return this.message.split('').reverse().join('') }

}

}

</script>

Como puedes apreciar las propiedades computadas son realmente funciones en las cuales se le establecen comparaciones u otra lógica elaborada, en este ejemplo, invertir una cadena de texto; en la propiedad computada anterior colocamos una impresión por consola:

src\components\ComputedComponent.vue

reversedMessage: function () {![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.038.png)

console.log('reversedMessage')

return this.message.split('').reverse().join('') }

Y desde el template, empleamos la propiedad computada como si se tratara de una variable reactiva en Vue; puedes ver que imprimimos la misma varias veces en el template, específicamente, 3 veces:

<template>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.039.png)

<ul>

<li> {{ reversedMessage }} </li>

<li> {{ reversedMessage }} </li>

<li> {{ reversedMessage }} </li> </ul>

</template>

Sin embargo, si inspeccionamos la consola, veremos que solamente aparece una sola impresión: reversedMessage![ref3]

Por lo tanto, podemos decidir que la función de la propiedad computada se imprimió una sola vez, como comentamos antes, la propiedad computada solamente se evalúa una vez sin importar las veces que se emplee la propiedad computada y solamente se volverá a ejecutar cuando alguno de los valores que contiene la misma cambia, en este ejemplo sería la variable **message**; por lo tanto, si variamos la misma, operación que podemos hacer mediante el **v-model**, por ejemplo para agregar una letra más, veremos que la propiedad computada se vuelve a invocar, pero, solamente una vez:

<template>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.040.png)

<ul>

<li> {{ reversedMessage }} </li>

<li> {{ reversedMessage }} </li>

<li> {{ reversedMessage }} </li> </ul>

**<input type="text" v-model="message">** </template>

Por el contrario, si empleamos un método en su lugar, veríamos que se innova tantas veces referenciamos el mismo en el template:

<template>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.041.png)

<ul>

<li> {{ reversedMessageByMethod() }} </li>

<li> {{ reversedMessageByMethod() }} </li>

<li> {{ reversedMessageByMethod() }} </li> </ul>

</template>

Entonces, en definitiva, si necesitas realizar operaciones sobre alguna variable reactiva de Vue, el uso de las propiedades computadas son tu mejor opción no solamente por su eficiencia si no porque permiten hacer una separación entre la lógica que siempre debería estar en la sección de script y el template.

Más información en:

<https://vuejs.org/guide/essentials/computed.html>

<a name="_page33_x28.35_y42.89"></a>Props

Como se comentó anteriormente, muchas veces es necesario pasar datos entre componentes, Vue es una tecnología modular en la cual se construyen cada uno de los elementos de interfaz gráfica mediante componentes, específicamente de un componente padre a un componente hijo.

Las propiedades se definen en el objeto props de un componente y se pasan al componente hijo como atributos personalizados.![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.042.png)

Los props se pueden utilizar para pasar datos simples como cadenas y números, así como objetos y matrices más complejos.![ref11]

En la mayoría de los casos es necesario pasar datos entre componentes para poder construir correctamente los elementos de interfaz gráfica y con esto que la aplicación funcione como se espera; el uso de los **props** es la forma predeterminada que tenemos para comunicar entre componentes.

Los **props** no son más que atributos que definimos a nivel del componente cuando instanciamos el mismo desde el componente padre; por ejemplo:

src\components\props\ParentComponent.vue

<template>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.044.png)

<div>

<h1>{{ title }}</h1>

<ChildComponent **:message="message"** /> </div>

</template>

<script>

import ChildComponent from './ChildComponent.vue';

export default {

components: {

ChildComponent,

},

data() {

return {

title: 'Props in Vue',

message: 'Data from parent', };

},

};

</script>

Y los mismos deben de estar registrados en el componente hijo:

20
}![ref3]

src\components\props\ChildComponent.vue

<template>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.045.png)

<div>

<p>{{ message }}</p> </div>

</template>

<script>

export default {

props: {

**message: String,** },

};

</script>

Los componentes de Vue requieren una declaración explícita de props para que Vue sepa cuales props serán pasados al componente.![ref11]

Hay varias opciones disponibles con los **props**, podemos definir los **props** que vamos a recibir como un array: props: ['foo'],![ref1]

O una mejor opción, es especificando como si fuera un objeto en la cual se puede especificar datos sobre los **props**, como el tipo de dato:

export default {![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.020.png)

props: {

title: String,

likes: Number }

}

Como puedes apreciar, aparte de indicar el nombre del atributo/prop, también se especifica el tipo de dato; también, podemos definir una serie de opciones para los **props** como un valor por defecto:

export default {![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.011.png)

props: {

title: {

**default: 'Title'** },

}

}

Validaciones para indicar el formato u otro:

export default {![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.046.png)

props: {

title: {![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.047.png)

**validator(value, props) {**

**return value.length > 5 }**

},

}

}

En el cual, puedes colocar cualquier tipo de validación y si el prop no pasa las validaciones, verás una excepción por la consola del navegador como la siguiente:

main.js:4 [Vue warn]: Invalid prop: custom validator check failed for prop "message".![ref12]

at <ChildComponent message="Data from parent" >

at <ParentComponent>

at <App>

O si es requerido o puede ser nulo:

export default {![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.049.png)

props: {

title: {

type: String,

**required: true** },

}

}

O un tipo de dato por defecto:

export default {![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.039.png)

props: {

title: {

**type: String** },

}

}

<a name="_page35_x28.35_y573.73"></a>Ciclo de vida de los componentes

Los ciclos de vida de la aplicación de Vue son fundamentales para que podamos crear componentes de manera efectiva, en esencia, el ciclo de Vue son representamos por funciones que podemos emplear en los componentes para cuando se crea el componente, se elimina, entre otros:

export default {![ref9]

mounted() {

console.log(`the component is now mounted.`) }

Por ejemplo, una operación muy común es que, cuando se carga el componente, queremos inicializar los datos conectándonos a alguna API; supón que tenemos un componente de listado el cual se construye a partir de un JSON array provisto por una Api Rest la cual se debe de consumir al momento de cargar el componente, en estos casos, podemos emplear la función de **mounted()**:

export default {![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.011.png)

mounted() { this.$axios.get(url).then((res) => {

this.dataList = res.data;

});

}

}

Para obtener más información, puedes consultar la documentación oficial: <https://vuejs.org/api/options-lifecycle>

<a name="_page36_x28.35_y292.30"></a>Instalar plugins

Vue tiene una cantidad inmensa de plugins que tenemos disponibles para realiza toda clase de operaciones; los plugins en Vue, usualmente tienen una configuración como la siguiente:

src\main.js

**import PLUGIN from 'PLUGIN';![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.050.png)**

const app = createApp(App) **app.use(PLUGIN)**

app.mount('#app')

En donde, en el archivo que se crea la aplicación en Vue, se emplea la función de **use()**, para registrar el plugin y con esto, poder emplear las características del plugin que hayamos instalado, a lo largo del libro, instalaremos algunos plugins para potenciar la experiencia.

<a name="_page36_x28.35_y550.61"></a>Vue Router

Por defecto, al crear una aplicación en Vue, solamente tenemos una página, es decir, no podemos navegar a otras ventanas de la aplicación y es aquí donde entran plugins como Vue Router que es el enrutador oficial de Vue.js, es decir, es el que nos permite crear páginas para poder navegar entre distintas páginas que a la final, son componentes en Vue; Vue Router no forma parte de Vue y se debe de instalar mediante una dependencia, es decir, un plugin; Vue Router fue desarrollado por el mismo equipo que desarrolló Vue por lo tanto, su integración es perfecta con el framework.

Algunas de las características de Vue Router incluyen:

- Mapear rutas de manera anidadas.
- Enrutamiento dinámico.

24

- Configuración de enrutador modular y basado en componentes.
- Parámetros de ruta, consulta, comodines o almohadillas.
- Efectos de transición entre vistas.

Vue Router lo emplearemos más adelante y por lo tanto, veremos una adaptación práctica del plugin. <a name="_page37_x28.35_y101.08"></a>Axios

Otra biblioteca que no puede faltar es la de axios, que es la biblioteca empleada por excelencia para realizar solicitudes HTTP, es decir, para hacer peticiones GET, POST, PUT, PATCH o DELETE y es una alternativa a la API incorporará en los navegadores webs modernos mediante la función de **fetch()**; algunas ventajas que tenemos al usar axios en vez de **fetch()**:

- Sintaxis más simple: Axios ofrece una sintaxis más simple y fácil de emplear; en axios ya existen métodos que tienen su equivalente con la petición HTTP como lo son **axios.get()** o **axios.post()**.
- Interceptores de solicitud y respuesta: Axios permite agregar interceptores a las solicitudes y respuestas HTTP. Esto significa que puede agregar lógica personalizada para manejar errores, autenticación, etc.
- Transformación automática de datos a formato JSON que es el formato más empleado para compartir datos en las API RESTs.
- Compatibilidad con navegadores antiguos, al ser una biblioteca que se instala en un proyecto de JavaScript, es posible emplearlo en una gran cantidad de navegadores en donde el fetch no es soportado.

Axios ni es un plugin específico para Vue, si no, para JavaScript, es posible emplearlo mediante Node o similares

- mediante la CDN, más adelante mostraremos cómo emplear esta biblioteca.

<a name="_page37_x28.35_y353.14"></a>Compartir datos entre componentes

Existen dos formas predeterminadas para poder compartir datos entre componentes, el que tenemos por defecto es el de los props, que presentamos anteriormente, pero, también podemos emplear plugins adicionales como el de Vuex o Pinia que son manejadores de estados.



|Un manejador de estados es una herramienta que se utiliza en el desarrollo de aplicaciones para manejar el|
| - |
|estado de la aplicación.|
|El estado de una aplicación corresponde a los datos que se manipulan y modifican en la misma, mientras la|
|aplicación se ejecuta.|

Al igual que Vue Router, son plugins creados específicamente para Vue y en el capítulo 5 veremos cómo emplear Pinia como manejador de estado.

<a name="_page37_x28.35_y557.66"></a>Reactividad en los arrays

Internamente Vue escucha los cambios que hagamos en las variables reactivas, cuando son números, booleanos

- string la detección de cambios es directa y no tiene complicaciones, sin embargo, cuando usamos arrays debemos de emplear ciertas funciones para que Vue detecte los cambios y realice los cambios en el bloque de script y/o template.

  Por ejemplo, teniendo el siguiente script:

  <template> <table >![ref13]

<thead>

26

<tr>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.052.png)

<th>Id</th>

<th>Title</th>

<th>Actions</th>

</tr>

</thead>

<tbody>

<tr v-for="(d, index) in data" :key="d.id">

<td>{{ d.id }}</td>

<td>{{ d.title }}</td>

<td>

<button @click="remove(index)">Delete</button> </td>

</tr>

</tbody>

</table>

</template>

<script>

export default {

data() {

return {

data: [

{

id: 1,

title: 'Test 1'

},

{

id: 2,

title: 'Test 2'

}

],

};

},

methods: {

remove(index){

**delete this.data[index]**

}

},

}; </script>

Veremos que los cambios no son detectados por Vue al momento de eliminar elementos del array; ya que no todas las funciones o mecanismos que existen en JavaScript para manipular los arrays son detectadas por Vue; funciones como:

- pop()
- shift()
- splice
- Asignaciones directas
- push()

Si son detectadas por Vue al momento de hacer el rastreo de variables reactivas de tipo array. Así que, los cambios en el script anterior quedan como:

<template>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.053.png)

<button @click="add()">Add</button>

<table >

<thead>

<tr>

<th>Id</th>

<th>Title</th>

<th>Actions</th>

</tr>

</thead>

<tbody>

<tr v-for="(d, index) in data" :key="d.id">

<td>{{ d.id }}</td>

<td>{{ d.title }}</td>

<td>

<button @click="remove(index)">Delete</button> </td>

</tr>

</tbody>

</table>

</template>

<script>

export default {

data() {

return {

data: [

{

id: 1,

title: 'Test 1'

},

{

id: 2,

title: 'Test 2'

}

],

};

},

methods: {

add(){

**this.data.push(**

**{**

**id: Date(), title: 'Test'![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.054.png)**

**}**

**)**

**/\* this.data = [].concat(this.data, {**

**id: Date(),**

**title: 'Test'**

**}); \*/**

**/\* this.data = [...this.data,**

**{**

**id: Date(),**

**title: 'Test'**

**}];\*/**

**/\* this.data[this.data.length] =**

**{**

**id: Date(),**

**title: 'Test'**

**}\*/**

},

remove(index){

**this.data.splice(index,1)**

}

},

}; </script>

Y en esta oportunidad veremos que la reactividad de Vue funciona perfectamente.

<a name="_page40_x28.35_y516.61"></a>Ref()

Mediante la función **ref()** podemos crear variables reactivas sin necesidad de definirlas en el bloque de **data()** esto es particularmente útil cuando empleamos el modo de composición de Vue o al momento de emplear manejadores de estados como Pinia.

<a name="_page40_x28.35_y598.76"></a>Símbolo de @ para importar componentes o archivos

Como veremos más adelante, al desarrollar aplicaciones en Vue se emplea el símbolo de arroba @ como un atajo para referirse a la ruta base del proyecto; luego, se especifica la ruta al componente que se desea importar; esto es extremadamente útil cuando estamos empleando importaciones en subcarpetas.

Por ejemplo:

</script>import MyComponent from '@/components/MyComponent.vue';![ref14]

<a name="_page41_x28.35_y28.35"></a>Composition API Por hacer

<a name="_page41_x28.35_y65.41"></a>Option API

Por hacer

<a name="_page41_x28.35_y118.46"></a>Opciones principales en el bloque se template

En este apartado, veremos las opciones más comunes que podemos realizar en los bloques de template, como el uso de directivas como los condicionales, for, impresiones e importaciones.

<a name="_page41_x28.35_y192.72"></a>Impresiones

Como comentamos anteriormente, para imprimir alguna primitiva como texto, número o boolean empleamos las dobles llaves:

{{var}}![ref8]

<a name="_page41_x28.35_y289.68"></a>Condicionales

En Vue, existen dos formas de emplear condicionales mediante las directivas **v-if** y **v-show**; ambas, son directivas que permiten evaluar condiciones de verdadero y falso para mostrar u ocultar elementos HTML.

La principal diferencia entre ambas es que **v-if** elimina el elemento del DOM si la expresión es falsa, mientras que **v-show** simplemente lo oculta el mencionado elemento.

<a name="_page41_x28.35_y400.93"></a>v-if

Al igual que en Vanilla JavaScript podemos emplear condicionales en los templates cuyos usos son iguales a los que hacemos en vanilla JavaScript, pero, estos los podemos establecer en los templates de la siguiente manera:

<template>![ref2]

<h4 v-if="true">Is True</h4> </template>

Para hacerlo más interesante, vamos a crear una propiedad a la cual evaluar:

<template>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.056.png)

<h4 v-if="counter > 10">Counter is greater than 10</h4> </template>

<script>

export default {

data: function(){

return {

counter: 11

}

},

}

</script>![ref3]

En este ejemplo, es una propiedad entera pero, puede ser de cualquier otro tipo y como queramos evaluar los condicionales, es exactamente igual a como hacemos en vanilla JavaScript:

if(counter > 10) {![ref6]

// TODO

}

Para hacerlo aún más interesante, vamos a implementar un evento click:

<template>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.057.png)

<h4 v-if="counter > 10">Counter is greater than 10</h4> <p>Counter is: {{ counter }}</p>

**<button @click="addOne">+1</button>**

</template>

<script>

export default {

data: function(){

return {

**counter:0**

}

},

**methods: {**

**addOne(){**

**this.counter++**

**}**

**},**

}

</script>

También tenemos la cláusula del **else**:

<template>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.058.png)

<h4 v-if="counter > 10">Counter is greater than 10</h4> **<h4 v-else>Counter is less than or equal 10</h4>** <p>Counter is: {{ counter }}</p>

<button @click="addOne">+1</button>

</template>

Si la condición del if es verdadera:

counter > 10![ref1]

Lo que va a ocurrir cuando el valor de **counter** sea igual o mayor a 11, veremos: Counter is greater than 10![ref8]

Y si es falso:

Counter is less than or equal 10 ![ref8]<a name="_page43_x28.35_y72.25"></a>V-show

En el caso de que necesitemos que siempre se renderice el HTML (o no nos importa que se renderice) podemos emplear la directiva **v-show** en su lugar:

<h4 v-show="true">Vue is fantastic</h4>![ref1]

Como puedes apreciar, si la propiedad es TRUE, se renderiza el contenido, al igual que con los **v-if**: <h4>Vue is fantastic</h4>![ref3]

Si es FALSE, veremos que el contenido se renderiza pero se oculta mediante la siguiente regla CSS: <h4 style="display: none;">Vue is fantastic</h4>![ref14]

Con los **v-show** podemos también ocultar elementos; la diferencia radica en que con los **v-show** se oculta el elemento y no se elimina del DOM como en el caso del **v-if**.

<a name="_page43_x28.35_y325.11"></a>v-for

Podemos usar la directiva **v-for** para iterar una lista de elementos en un **array** de JavaScript; su uso es muy similar al que tenemos en Vanilla JavaScript:

<template>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.059.png)

<ul>

<li v-for="i in items">

{{i}}

</li>

</ul>

</template>

<script>

export default {

data: function(){

return {

items: [

'Item 1', 'Item 2', 'Item 3', 'Item 4',

]

}

},

}

</script>

Si intentamos ejecutar el código anterior, veremos un error como el siguiente en la consola del navegador: Elements in iteration expect to have 'v-bind:key'![ref8]

Adicional a la directiva **v-for**, la misma tiene que estar acompañada de una key o clave que debe ser única para cada elemento renderizado en la lista, usualmente se coloca el índice del ciclo o un dato que contenga el elemento iterable (array en este ejemplo) que estamos iterando:

<li v-for="i in items" **v-bind:key="i"**>![ref8]

Y veremos el siguiente contenido renderizado en el navegador:

<ul>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.060.png)

<li>Item 1</li>

<li>Item 2</li>

<li>Item 3</li>

<li>Item 4</li> </ul>

Los **v-for** también admite un segundo argumento opcional para el índice del elemento actual: <li v-for="(i, **index**) in items" v-bind:key="i">![ref8]

Quedando el ejercicio como:

<template>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.047.png)

<ul>

<li v-for="(i, index) in items" v-bind:key="i">

{{index}} - {{i}}

</li>

</ul>

</template>

Y veremos el siguiente contenido renderizado en el navegador:

<ul>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.061.png)

<li>0 - Item 1</li>

<li>1 - Item 2</li>

<li>2 - Item 3</li>

<li>3 - Item 4</li> </ul>

<a name="_page44_x28.35_y630.59"></a>v-for con un objeto

Puedes usar el **v-for** para iterar a través de las propiedades de un objeto:

<ul>![ref2]

<li v-for="value in object">

{{ value }}

</li>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.062.png)

</ul>

<script>

export default {

data: function(){

object: {

name: 'Andrew', lastname: 'Cruz', age: 30

},

}

}

</script>

O un listado de objetos:

<template>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.063.png)

<ul>

<li v-for="i in items" v-bind:key="i">

{{i.body}}

</li>

</ul>

</template>

<script>

export default {

data: function(){

return {

items: [

{'body':'Item 1','id':1}, {'body':'Item 2','id':2}, {'body':'Item 3','id':3},

{'body':'Item 4','id':4},

]

} },

} </script>

No es recomendable usar los condicionales y el ciclo v-for en una misma etiqueta HTML: <li v-for="i in items" v-if="item.visible" v-bind:key="i.text">![ref1]

Si intentamos colocar la directiva v-if y v-for en un mismo elemento HTML, veremos un mensaje como el siguiente:

This 'v-if' should be moved to the wrapper element![ref1]

El cual indica que deberíamos de mover en **v-if** a un elemento interno del **v-for**:

<template>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.064.png)

<div>

<ul>

<template v-for="i in items" v-bind:key="i.text">

<li v-if="i.visible">{{ i.body }}</li> </template>

</ul>

</div>

</template>

<script>

export default {

data() {

return {

items: [

{ body: "Item 1", visible: true },

{ body: "Item 2", visible: false },

{ body: "Item 3", visible: true },

{ body: "Item 4", visible: false }, ],

};

},

};

</script>

En el ejemplo anterior usamos un elemento template con el cual podemos evitar renderizado un elemento HTML adicional, en otras palabras, el elemento template no se renderiza.

<a name="_page46_x28.35_y471.11"></a>V-model

Como comentamos antes, los **v-model** en Vue son una directiva que permite crear un enlace bidireccional entre un valor de cualquier etiqueta HTML de un formulario como un SELECT, INPUT, TEXTAREA o similares:

<template>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.065.png)

<label>Name</label>

<input type="text" v-model="name"> <label>Sex</label>

<select type="text" v-model="sex">

<option value="M">Male</option>

<option value="F">Female</option> </select>

<label>About</label>

<textarea v-model="about"></textarea> </template>

Y una variable reactiva en Vue:

<script>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.066.png)

export default { data() {

return {

name:'',

sex:'',

about:'' }

},

}

</script>

Vue monitorea el elemento del campo de formulario y lo vincula con la variable reactiva y actualiza la variable cuando hay un cambio en el elemento de formulario vinculado y viceversa; puedes probar ejecutar el siguiente ejercicio:

<template>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.067.png)

<label>Name</label>

<input type="text" v-model="name"> <label>Sex</label>

<select type="text" v-model="sex">

<option value="M">Male</option>

<option value="F">Female</option> </select>

<label>About</label>

<textarea v-model="about"></textarea>

<button @click="send">Send</button> </template>

<script>

export default {

data() {

return {

name:'',

sex:'',

about:''

}

},

methods: {

send(){

alert(`

name ${this.name}

sex ${this.sex}

about ${this.about}

`)

}

},![ref15]

} </script>

Y verás que, al establecer los datos en el **v-model**, se actualizan automáticamente en las variables reactivas; aunque, también podemos establecer valores desde el bloque de script, por ejemplo, para restablecer el formulario:

<template>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.069.png)

<label>Name</label>

<input type="text" v-model="name"> <label>Sex</label>

<select type="text" v-model="sex">

<option value="M">Male</option>

<option value="F">Female</option> </select>

<label>About</label>

<textarea v-model="about"></textarea>

<button @click="send">Send</button>

<button @click="reset">Reset</button> </template>

<script>

export default {

data() {

return {

name:'',

sex:'',

about:''

}

},

methods: {

send(){

alert(`

name ${this.name}

sex ${this.sex}

about ${this.about}

`)

},

reset(){

this.name=''

this.sex='M'

this.about=''

}

},

}

</script>

<a name="_page49_x28.35_y28.35"></a>Eventos

Los eventos son el mecanismo que tenemos para que el usuario pueda interactuar con la aplicación, los eventos en Vue, son los mismos que tenemos disponibles en vanilla JavaScript como lo son el evento click y de teclados; además de esto, tenemos muchas opciones para por ejemplo escuchar la tecla enter, ejecutar el evento una sola vez, entre otras.

Usamos la directiva **v-on** para escuchar los eventos del DOM que ocurren en JavaScript o la forma recortada (shortcut) **@**.

Para usar los eventos, debemos de usar el siguiente atributo: v-on

Seguido del evento que queremos usar, por ejemplo, el click: v-on:click

El shortcut al v-on viene siendo el carácter del arroba:

@

Así que, si queremos emplear el evento click queda como: @click

Para definir el cuerpo del evento, tenemos dos formas:

**En línea**: Este es el formato más sencillo que tenemos disponible, consiste en que podemos indicar la operación a realizar directamente en el atributo **@click**, es decir, como parte del valor:

js![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.070.png)

const count = ref(0)

template

<button @click="**count++**">Add 1</button> <p>Count is: {{ count }}</p>

**Mediante métodos**: Esta es un esquema similar al empleado en vanilla JavaScript, que consiste que mediante el atributo de **@click** especificamos un método el cual se va a invocar al momento de ocurrir el evento click.

js![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.071.png)

const name = ref('Vue.js')

function hello() {

alert(`Hello ${name.value}!`)

}

template

<button @click="hello">Hello</button>

Otros eventos que podemos usar son, el de teclado: <input type="text" @keyup="submit" /> ![ref1]Capturar la tecla enter:

<input type="text" @keyup.enter="submit" />![ref3]

<a name="_page50_x28.35_y145.25"></a>Alias Key

Aparte de escuchar la tecla enter, Vue proporciona alias para las claves más utilizadas:

- .enter
- .tab
- .delete (captura las teclas "Eliminar" y "Retroceso")
- .esc
- .space
- .up
- .down
- .left
- .right

<a name="_page50_x28.35_y329.22"></a>Teclas modificadoras del sistema

En Vue, también tenemos acceso a otras teclas conocidas como modificadores para activar detectores de eventos del mouse o del teclado solo cuando se presiona la tecla modificadora correspondiente:

- .ctrl
- .alt
- .shift
- .meta

Por ejemplo:

<input type="button" @keyup.ctrl="toDo" /> ![ref4]<a name="_page50_x28.35_y513.46"></a>Eventos personalizados

También podemos crear eventos personalizados en Vue, los cuales son muy útiles para comunicar componentes hijos con el padre; para eso, en el componente padre, definimos el evento al momento de cargar el hijo:

<template>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.072.png)

<div>

<h1>{{ title }}</h1>

<ChildComponent **@event-in-child="callback"** /> </div>

</template>

<script>

import ChildComponent from './ChildComponent.vue';

export default {![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.073.png)

components: {

ChildComponent,

},

methods: {

callback(){

console.log('Custom event') }

},

};

</script>

Desde el componente hijo, puedes emitir eventos personalizados usando el método **$emit**:

<template>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.074.png)

<button @click="$emit('eventInChild')">Click this button</button> </template>

Puedes pasar parámetros mediante el evento:

<script>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.075.png)

import ChildComponent from './ChildComponent.vue';

export default {

components: {

ChildComponent,

},

methods: {

callback(**p1**){

console.log('Custom event '+p1) }

},

};

</script>

Y en el hijo:

<template>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.076.png)

<button @click="$emit('eventInChild'**, 'param'**)">Click this button</button> </template>

Puedes pasar los parámetros que sean necesarios separándolos por comas:

<template>![ref5]

<button @click="$emit('eventInChild'**, 'param', n1, \*\*\* , nx**)">Click this button</button> </template>

Los eventos personalizados son muy útiles para pasar datos o estados desde el componente hijo al padre, al ser la comunicación inversa (ya que es el componente padre el que carga al hijo).

<a name="_page52_x28.35_y57.44"></a>Opciones principales en el bloque de style

Como función importante que tenemos disponible en el bloque de style, cuyo propósito es definir el estilo o CSS para el componente es, si quieres que el estilo sea global a la aplicación:

<style>![ref12]

button{

background: green; }

</style>

O local al componente:

<style **scoped**>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.077.png)

button{

background: green;

} </style>

<a name="_page52_x28.35_y337.99"></a>Conclusiones

En los siguientes capítulos crearemos algunos proyectos reales con alcance limitados en los cuales usaremos Vue junto con otras tecnologías web como lo son Oruga UI y Naive UI; ambas tecnologías consiste en un conjunto de componentes como lo son tablas, campos de formularios, botones entre otros listos para emplear, realmente podemos usar el HTML tradicional, pero, para evitar trabajar con interfaces feas y conocer el verdadero potencial que tiene Vue como tecnología que NO solamente es la tecnología en sí sino, su entorno y conocer el potencial de esta tecnología, vamos a enriquecer los proyectos con estas tecnologías anteriormente mencionadas, además de otras como axios para realizar peticiones HTTP, tailwind CSS como framework CSS basado en clases de utilidades entre otros.

Es importante mencionar que el libro está enfocado a trabajar con Vue y tecnologías relacionadas, pero para las bases del framework como lo son Node y su ecosistema, se da por hecho de que el lector tiene al menos conocimientos básicos.

Finalmente, hasta este capítulo hemos visto en base a pequeños ejemplos el uso de las principales características de Vue, vamos a empezar a integrar todos estos pequeños desarrollos a proyectos reales con alcance limitado para tener un mejor enfoque del uso de la tecnología en proyectos reales y conocer su potencial; los proyectos que vamos a implementar en los siguientes dos capítulos, fueron tomados y adaptados de otros de mis libros, específicamente el de primeros pasos con Laravel y Django en los cuales, implementamos una API Rest en dichas tecnologías y luego es consumida mediante Vue.

**Capítulo 3: Consumir una Rest Api tipo CRUD con <a name="_page53_x28.35_y61.47"></a>Vue y Oruga UI**

En este apartado, vamos a crear una aplicación tipo CRUD empleando Vue y una Rest Api tipo CRUD existente; es decir, una Api Rest con un alcance limitado que no implementaremos nosotros si no, simplemente usaremos; como comentamos anteriormente, usaremos Oruga UI como framework web del lado del cliente basado en componentes; todos estos detalles los introduciremos más adelante en el capítulo.

<a name="_page53_x28.35_y158.78"></a>Api CRUDCRUD primeros pasos

En este capítulo, vamos a crear un sencillo proceso CRUD, consumiendo una Rest API de ejemplo, como comentamos antes, no vamos a crear una si no, emplear una solución existente como viene siendo la siguiente:

<https://crudcrud.com/>

Como puedes ver en la página oficial, es una Api Rest tipo CRUD en los cuales podemos crear o administrar cualquier entidad como publicaciones, artículos u otros productos; puedes verla como las colecciones que usamos en las base de datos de tipo no relacionales como MongoDB, en los cuales, tenemos una entidad como la siguiente, para representar una publicación:

{![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.078.png)

"id": 6,

"title": "Un0vOn6bwUqG9JhGPXDL",

"slug": "un0von6bwuqg9jhgpxdl",

"description": "Lorem ipsum dolor sit amet consectetur, adipisicing elit. Vitae ",

}

O una categoría: {![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.079.png)

"id": 1,

"title": "Category 1"

}

Realmente podemos crear cualquier cosa al ser un sistema bastante flexible.

Para emplear la APi anteriormente mencionada no es necesario registrarse y tiene una cuenta que podemos emplear de manera gratuita; diariamente (cada 24 horas), podemos realizar 100 peticiones que pueden ser de tipo GET, POST, PUT o DELETE para poder administrar nuestras entidades; si vamos a la página anterior, veremos que automáticamente genera una clave de cliente que es la que debemos de usar; por ejemplo:

324c9410858844feabe1965c57174614

Si por ejemplo queremos crear un POST, podemos usar una URL como la siguiente: POST https://crudcrud.com/api/324c9410858844feabe1965c57174614/**post**

Ya partir de aquí, la Rest Api genera tanto un identificador para la publicación para que podamos obtener ya sea el detalle o para actualizarla o eliminar el recurso que hayamos creado y obtener un listado de todos los elementos usando los diferentes tipos de peticiones:



|Action|HTTP|Payload|URL|Description|
| - | - | - | - | - |
|Create|POST|json|/<resource>|Create an entity represented by the JSON payload.|
|Read|GET|-|/<resource>|Create an entity represented by the JSON payload.|
|Read|GET|-|/<resource>/<id>|Get a single entity.|
|Update|PUT|json|/<resource>/<id>|Update an entitiy with the JSON payload.|
|Delete|DELETE|-|/<resource>/<id>|Delete an entity.|

Por ejemplo, si quisiéramos crear un post, podríamos emplear algo como: POST https://crudcrud.com/api/324c9410858844feabe1965c57174614/**post**

O una categoría:

POST https://crudcrud.com/api/324c9410858844feabe1965c57174614/**category** Lo importante es notar, el dominio base: **https://crudcrud.com/api/**324c9410858844feabe1965c57174614/post

Más el ID:

https://crudcrud.com/api/**324c9410858844feabe1965c57174614**/post

Más el recurso: https://crudcrud.com/api/324c9410858844feabe1965c57174614/**post**

Y a esos recursos es a los cuales enviaremos peticiones HTTP mediante axios.

Con todo esto, podemos abstraernos de implementar una Rest API y consumimos una existente. También, usaremos un framework CSS para poder emplear componentes existentes con un estilo aplicado y de esta manera poder enfocarnos a lo que nos interesa, que es, crear una aplicación en Vue para consumir una Rest API![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.080.png)![ref16]

Si al momento de utilizar la API de CRUDCRUD vez un error como el siguiente:

Error de tipo 400: Endpoint has expired.![ref3]

Prueba limpiar las cookies del navegador o emplear otro navegador para que genere otro identificador que puedas establecer en la aplicación en Vue.

<a name="_page55_x28.35_y101.34"></a>Crear el proyecto

Comenzamos creando un proyecto en Vue mediante la CLI como se mostró en el capítulo 1 cuyo nombre sugerido es **vueoruga**.

Desde el componente principal, vamos a cargar el componente de listado: src\App.vue

<template>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.082.png)

<div>

<h1>Principal</h1> <list/>

</div> </template>

<script>

import List from './components/ListComponent.vue' export default {

components:{

List

}

}

</script>

Finalmente, creamos el componente de listado: src\components\ListComponent.vue

<template>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.083.png)

<div> <h1>List</h1>

</div> </template>

Si ejecutamos la aplicación, veremos:

![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.084.png)

Figura 3-1: Componente de listado

<a name="_page56_x28.35_y162.17"></a>Configurar proyecto con CRUDCRUD

Como comentamos antes, para emplear la API de CRUDCRUD, debemos de emplear un identificador en todas las peticiones que vayamos a realizar; para evitar definir esta URL desde cero cada vez que vayamos a emplear la misma, almacenaremos en alguna variable global esta URL junto con el ID para que pueda ser fácilmente consumida:

src\App.vue

\*\*\*![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.085.png)

<script>

const urlCRUD = "https://crudcrud.com/api/324c9410858844feabe1965c57174614";

export default {

data() {

return {

urlCRUD: urlCRUD,

urlCRUDPost: urlCRUD + "/post", };

},

};

</script>

Hay muchas formas de crear variables globales que puedan ser consumidas desde todos los componentes de la aplicación, una forma bastante simple consiste en el componente padre, que es el **App.vue**, crear las variables reactivas y luego mediante:

this.$root.urlCRUDPost![ref3]

podamos consumir estas variables reactivas desde los componentes hijos.

<a name="_page56_x28.35_y618.34"></a>Configurar proyecto en Vue 3 con Oruga UI

Ahora vamos a configurar una librería para trabajar con los elementos de UI; en este caso vamos a emplear Oruga UI.

Oruga es una biblioteca liviana de componentes de interfaz de usuario para Vue.js sin dependencia de CSS.![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.086.png)

No depende de ningún estilo específico o framework CSS (como Bootstrap, Bulma, TailwindCSS, etc.) y no proporciona ningún sistema de cuadrícula o utilidad CSS, solo ofrece un conjunto de componentes fáciles de personalizar; por lo tanto, si quieres usar un estilo personalizado, puedes crear hojas de estilo personalizadas, o usar el opcional que te ofrece Oruga UI o integrar un framework CSS.![ref16]

En pocas palabras, Oruga nos trae un conjunto de componentes de UI que podemos emplear de manera gratuita como botones, tablas, switch, loading y un CSS básico opcional.

Puede ser la lista completa en la documentación oficial en: <https://oruga.io/documentation/>

Por lo tanto, pudieras emplear otros frameworks CSS junto a Oruga UI como Bootstrap, Tailwind.css, Bulma, etc. Aunque, de manera opcional, podemos emplear un CSS básico de Oruga UI, que es el que vamos a usar en este libro.

<https://oruga.io/>

Instalamos Oruga UI con:

$ npm install @oruga-ui/oruga-next --save![ref3]

Oruga por defecto no trae una hoja de estilo aplicada, pero, tenemos una que provee de manera opcional y que usaremos en el proyecto; para ello, debemos de instalarla con:

$ npm install @oruga-ui/theme-oruga![ref17]

Configuramos el **main.js** agregando Oruga UI como un plugin más y el CSS adicional que vamos a usar: src\main.js

import { createApp } from "vue";![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.088.png)

import Oruga from '@oruga-ui/oruga-next'

import '@oruga-ui/theme-oruga/dist/oruga-full.min.css';

import App from "./App.vue"

const app = createApp(App).use(Oruga)

app.mount("#app")

Ya en este punto haz algunas pruebas con componentes de Oruga; puedes incluirlos en tu **ListComponent.vue**:

![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.089.png)

Figura 3-2: Componente de Vue error inválido

El código de prueba sería el siguiente: src\components\ListComponent.vue

<template>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.090.png)

<div>

<h1>List</h1>

**<o-field label="Email" variant="danger" message="This email is invalid">**

**<o-input type="email" value="john@" maxlength="30"> </o-input> </o-field>**

**<o-button @click="clickMe">Click Me</o-button> </div>**

</template>

**<script>**

**export default {**

**methods: {**

**clickMe() {**

**alert("Clicked!"); },**

**},**

**};**

**</script>**

Prueba a comentar el CSS antes presentado, y veas el comportamiento de la aplicación; verás que el estilo se rompe y nuevamente esto es útil si quieres emplear una hoja de estilo adicional.

//import '@oruga-ui/theme-oruga/dist/oruga-full.min.css'; \*\*\*![ref10]

Puedes consultar la documentación oficial para conocer cómo puedes incluir clases adicionales y variar los colores oficiales.

Los iconos también son un tema aparte y si quieres emplearlos, tienes que instalar alguno de los siguientes paquetes disponibles:

- <https://materialdesignicons.com/>
- <https://fontawesome.com/>

<a name="_page59_x28.35_y101.08"></a>Generar un listado

Ahora sí, vamos a empezar a configurar nuestra aplicación, comenzando por el componente de listado que creamos anteriormente, para ello, vamos a realizar una petición por GET para obtener todas las publicaciones:

this.$axios.get(this.$root.urlCRUDPost).then((res) => {![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.091.png)

this.posts = res.data;

console.log(this.posts);

});

Sin embargo, si intentamos emplear axios desde un componente en Vue, te va a dar un error como el siguiente: VM409:1 Uncaught TypeError: Cannot read properties of undefined (reading 'get')![ref1]

Ya que, axios no existen en este contexto.

<a name="_page59_x28.35_y336.66"></a>Configurar axios

Usaremos axios como librería para realizar las conexiones HTTP, consumir una Rest API consiste en poder enviar peticiones a la misma, aunque pudiéramos emplear el API de fetch que ya la provee JavaScript sin necesidad de instalar herramientas adicionales, axios es una excelente opción que nos facilita un poco más el proceso,

Instalamos axios en el proyecto mediante: $ npm install axios![ref3]

Y como cualquier plugin, configuramos en el archivo principal de Vue para poder emplear a lo largo de la aplicación, específicamente en los componentes de Vue:

src\main.js

import { createApp } from "vue";![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.092.png)

import Oruga from '@oruga-ui/oruga-next'

import '@oruga-ui/theme-oruga/dist/oruga-full.min.css';

**import axios from 'axios'**

import App from "./App.vue"

const app = createApp(App).use(Oruga) **app.config.globalProperties.$axios = axios**

**window.axios = axios ![ref15]**app.mount("#app")

Desde el navegador, puedes inspeccionar el objeto devuelto al momento de realizar alguna conexión axios y realizar algunas pruebas con la misma; por ejemplo:

this.$axios.get(this.$root.urlCRUDPost).then((res) => {![ref13]

res

});

El objeto de respuesta, tiene una estructura como la siguiente:

\*\*\*![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.093.png)

data: [{…}]

headers: AxiosHeaders {content-type: 'application/json; charset=utf-8'}

request:

XMLHttpRequest {onreadystatechange: null, readyState: 4, timeout: 0, withCredentials: false, upload: XMLHttpRequestUpload, …}

status: 200

statusText: "OK"[[Prototype]]: Object

Como valor importante a evaluar, es el de status, en una respuesta exitosa, retorna una respuesta de tipo 200: res.status 200![ref1]

Para obtener los datos en formato JSON, colocamos:

res.data![ref7]

<a name="_page60_x28.35_y454.44"></a>Consumir Api mediante axios

Ahora si, en el componente de **ListComponent.vue**, vamos a desarrollar el siguiente script: src\components\ListComponent.vue

<script>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.094.png)

export default {

data() {

return {

posts: [],

isLoading: true, };

},

async mounted() { this.$axios.get(this.$root.urlCRUDPost).then((res) => {

this.posts = res.data.data;

console.log(this.posts); this.isLoading = false;![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.095.png)

}); },

}; </script>

Un par de propiedades, una para saber si estamos cargando o no la data, y la otra para almacenar nuestros posts. Luego, la función de tipo **mounted()** que se ejecuta cuando se monta el componente de Vue.

Vamos a usar el componente de tabla de Oruga UI, el cual puedes definir las columnas directamente en la data:

<script>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.096.png)

export default {

data() {

return {

data: [

{

id: 1,

first\_name: 'Jesse', \*\*\*

gender: 'Male'

},

\*\*\* {

id: 5,

first\_name: 'Anne', last\_name: 'Lee', \*\*\*

}

],

columns: [

{

field: 'id', label: 'ID', width: '40', numeric: true

},

{

field: 'first\_name',

\*\*\*

] }

}

} </script>

O por las columnas, como vamos a hacer nosotros:

<template>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.097.png)

<div>

<h1>List</h1>

<o-table :loading="isLoading" :data="posts.length == 0 ? [] : posts">

<o-table-column field="id" label="ID" numeric v-slot="p">

{{ p.row.\_id }}

</o-table-column>

<o-table-column field="title" label="Title" v-slot="p">

{{ p.row.title }}

</o-table-column>

<o-table-column field="posted" label="Posted" v-slot="p">

{{ p.row.posted }}

</o-table-column>

<o-table-column field="category" label="Category" v-slot="p">

{{ p.row.category\_id }}

</o-table-column>

</o-table>

</div>

</template>

Importante notar que, todos los componente de Oruga UI, comienzan con el prefijo de **o-**, en este caso el de la tabla tiene configuraciones adicionales para indicar bordeado, striped, efecto hover, entre otros y **:loading="isLoading"**.

Quedando como:

![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.098.jpeg)

Figura 3-3: Listados de publicaciones

<a name="_page62_x28.35_y595.77"></a>Instalar Material Design Icons

Vamos a instalar una iconográfica, como mencionamos antes podemos usar Font Awesome o Material Design Icons; vamos a emplear este último; simplemente lo instalamos:

$ npm install @mdi/font ![ref18]Y lo referenciamos:

src\main.js

//Material Design![ref10]

import "@mdi/font/css/materialdesignicons.min.css"

Y eso sería todo, ya con esto, Oruga UI detectará los iconos al momento de emplear sus componentes; por ejemplos el de listado que ya vamos a abordar.

Otro punto importante, cuando generemos los archivos de salida, veremos algo como:

│ /js/app.js │ ![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.100.png)715 KiB │

│ /js/main.js │

2\.24 MiB │

│ css/app.css │

32\.4 KiB │

│ **fonts/vendor/@mdi/materialdesignicons-webfont.eot?e044ed23c047e571c55071b6376337f9 │**

**1.09 MiB │**

**│ fonts/vendor/@mdi/materialdesignicons-webfont.ttf?5d42b4e60858731e7b6504400f7e3d8e │**

**1.09 MiB │**

**│ fonts/vendor/@mdi/materialdesignicons-webfont.woff2?606b16427a59a5a97afbe8bb5f985394 │ 353 KiB │**

**│ fonts/vendor/@mdi/materialdesignicons-webfont.woff?5dff34d5fed607519dcbc76eaf9fc5b9**

En los cuales puedes ver, las fuentes de los iconos generados.

<a name="_page63_x28.35_y410.80"></a>Demo: Paginación

Una de las limitantes que tenemos al usar la API de CRUDCRUD es que no podemos paginar los registros; por lo tanto, la siguiente implementación la puedes tomar de referencia cuando emplees tus APIs personalizadas.

Usamos el recurso de paginación para mostrar los posts, con la intención de mostrar los enlaces de paginación; la paginación con Oruga UI es muy sencilla y tiene un alto nivel de personalización; en nuestro componente de **ListComponent.vue**, vamos a definir el siguiente componente luego de la tabla:

<template>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.101.png)

<div>

<h1>List</h1>

<o-table

:loading="isLoading"

**:data="posts.current\_page && posts.data.length == 0 ? [] : posts.data"** >

\*\*\*

</o-table>

<br />

<o-pagination![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.102.png)

v-if="posts.current\_page && posts.data.length > 0" @change="updatePage"

:total="posts.total" v-model:current="currentPage"

:range-before="2"

:range-after="2"

order="centered"

size="small"

:simple="false"

:rounded="true"

:per-page="posts.per\_page"

\>

</o-pagination>

</div>

</template>

<script>

export default {

data() {

return {

posts: [],

isLoading: true,

currentPage:1, };

},

methods: {

updatePage(){

setTimeout(this.listPage, 100); },

listPage(){

this.isLoading = true; this.$axios.get("/api/post?page="+this.currentPage).then((res) => {sss

this.posts = **res.data**;

console.log(this.posts);

this.isLoading = false;

});

}

},

async mounted() {

this.listPage() },

};

</script>

**Explicación del código anterior**

Importante notar que, ya en el componente de paginación, tenemos muchos datos parámetros de personalización que puedes consultar en la documentación oficial de Oruga UI.

El componente de **o-pagination**, como puedes revisar en la documentación oficial, crea un conjunto o de enlaces de paginación; el componente recibe varios parámetros para personalizar el mismo; evaluemos los usados:

- El evento de **change**, se ejecuta cuando el usuario da un click sobre los enlaces de paginación, el evento recibe un parámetro opcional que viene siendo el índice de la página.
- En el **props** de total, indicamos el total de registros.
- Con el **v-model**, definimos la página actual, este parámetro va a cambiar cada vez que el usuario le dé un click a uno de los enlaces de paginación y se actualizará con dicho índice; por lo tanto, como referencia a la página actual, puedes emplear este **v-model**, o el parámetro del evento **change**.
- Luego definimos los rangos de cuantos enlaces quieres mostrar para los enlaces antes y después de la página seleccionada (**range-before="2"** y **range-after="2"**).
- Definimos la alineación y el tamaño del componente de paginación (**order="centered"** y **size="small"**).
- Definimos el diseño completo y redondeado (**simple="false"** y **rounded="true"**).
- Finalmente, define cuantos elementos quieres mostrar por página (**per-page**).

Recuerda que en la documentación oficial tienes muchas más opciones para personalización.

Otro punto importante en el código anterior, es que, en la propiedad de los posts, ahora tenemos tanto la información de los posts como de la paginación:

{![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.103.png)

"current\_page": 2,

"data": [

{

"id": 6,

"title": "Un0vOn6bwUqG9JhGPXDL",

"slug": "un0von6bwuqg9jhgpxdl",

"description": "Lorem ipsum dolor sit amet consectetur, adipisicing elit. Vitae ", }

...

],

"first\_page\_url": "http://laraprimerospasos.test/api/post?page=1",

"from": 5,

"last\_page": 8,

"last\_page\_url": "http://laraprimerospasos.test/api/post?page=8",

"links": [

{

"url": "http://laraprimerospasos.test/api/post?page=1",

"label": "&laquo; Previous",

"active": false

},

...

],

"next\_page\_url": "http://laraprimerospasos.test/api/post?page=3",

"path": "http://laraprimerospasos.test/api/post",

"per\_page": 4,![ref19]

"prev\_page\_url": "http://laraprimerospasos.test/api/post?page=1", "to": 8,

"total": 29

}

El código anterior, es el generado por una Api Rest creada en Laravel, en caso de que quieres a aprender a crear sistemas como estos, puedes adquirir otros de mis libros:

<https://desarrollolibre.net/libros/primeros-pasos-laravel>

Con la respuesta JSON anterior, en el **o-table**, debes de definir el **.data**, para acceder al detalle de los posts, que es lo que queremos trabajar en ese apartado.

Luego, creamos una función que es la que se encarga de actualizar los posts, cuando el usuario da click en una de las opciones, se ejecuta el evento de **change** y traemos los datos paginados usando el **v-model** de **currentPage**, que como comentamos, contiene el índice de la página actual; en este apartado fijate que empleamos una función de intermediario, llamado **updatePage()**,que luego llama a la función que trae los posts paginados mediante la función de **setTimeout()**, y esto se debe a que en la versión que se empleó de Oruga UI, existe un retraso al momento de actualizar el valor de **currentPage**.

Finalmente, la función de **listPage()** pasa el parámetro page para saber qué página va a solicitar al servidor; dicha función también se llama al momento de montar el componente:

async mounted() {![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.105.png)

this.listPage() },

Y visualmente, queda como:

![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.106.png)

Figura 3-4: Componente de paginación

<a name="_page66_x28.35_y538.43"></a>Ruteo con Vue Router

Vamos a necesitar crear más páginas o componentes para nuestra aplicación, en próximo sería el componente de formulario, para crear y actualizar posts, por lo tanto, vamos a necesitar emplear más de una página; por tal motivo, necesitamos usar un plugin que permita dicha característica, que sería el de Vue Router; que nos permite atar los componentes a una ruta que definamos.

<a name="_page66_x28.35_y641.77"></a>Instalación

Instalamos Vue Router en su última versión con: $ npm install vue-router@4![ref17]

<a name="_page67_x28.35_y28.35"></a>Definir rutas

Definimos en un archivo aparte las rutas que vamos a usar; en este caso un componente llamado **SaveComponent.vue** que todavía no existe y que ya vamos a crear en el siguiente apartado; pero, para que la aplicación no de un error, por referenciar un componente que no existe, coloca la referencia al único componente que sí existe, el de **ListComponent.vue**:

src\router.js

import { createRouter, createWebHistory } from "vue-router"![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.107.png)

import List from './components/ListComponent.vue' import Save from './components/SaveComponent.vue'

const routes = [

{

name:'list',

path:'/vue',

component: List },

{

name:'save', path:'/vue/save', component: Save

},

]

const router = createRouter({

history: createWebHistory(), routes: routes

})

export default router

Como puedes ver, cargamos un par de funciones, una para crear el componente de rutas, llamada **createRouter()** que recibe como parámetros:

1. El tipo de modo histórico; el recomendado **createWebHistory()** que hace que el ruteo se vea normal en la URL, pero, también puedes usar el de en base a hash con **createWebHashHistory().**
1. Las rutas.

La definición de las rutas que usamos pasa por definir:

- El nombre del componente (**name**).
- El path, para indicar la URI (**path**).
- El componente de Vue (**component**).

<a name="_page68_x28.35_y28.35"></a>Componente para el renderizado de los componentes

Ahora, necesitamos definir en el componente padre, el componente de **router-view** que es el nombre del componente que va a renderizar el componente cuando haga match entre la URL al momento de navegar y la URI definida en el **router.js**

src\App.vue

<template>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.108.png)

<div>

**<router-view></router-view>**

</div> </template> \*\*\*

<a name="_page68_x28.35_y227.01"></a>Establecer las rutas

Ahora, tenemos que consumir el módulo con nuestras rutas que creamos anteriormente, para esto, lo hacemos como cuando usamos cualquier plugin para Vue; tal cual hicimos con Oruga UI, cargando el componente y hacemos uso de la función **use()**.

src\main.js

**import router from "./router"![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.109.png)**

const app = createApp(App).use(Oruga)**.use(router)**

<a name="_page68_x28.35_y397.23"></a>Crear enlaces

Para poder navegar entre cada uno de nuestros componentes, tenemos que usar el componente de **RouterLink**; con la URI al componente que queremos navegar, o el nombre, ya que, estamos usando rutas con nombre, las usamos:

src\components\ListComponent.vue

<template>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.110.png)

<div>

<h1>Post list</h1>

**<router-link :to="{name:'save'}">Create</router-link>**

<o-table

:loading="isLoading"

:data="posts.current\_page && posts.length == 0 ? [] : posts" >

\*\*\*

**<o-table-column field="\_id" label="Actions" v-slot="p">**

**<router-link**

**class="mr-3"**

**:to="{ name: 'save', params: { id: p.row.\_id } }" >Edit</router-link![ref19]**

**>** </o-table>

\*\*\*

Importante notar que, creamos en la tabla, una columna para las opciones; de momento solamente colocamos un enlace que apunta al componente de **SaveComponent.vue** (que aún no hemos creado), pasando un parámetro del id, para que sepamos que post queremos editar.

<a name="_page69_x28.35_y160.59"></a>Componente para crear y editar post

Vamos a crear el componente para guardar publicaciones desde la aplicación en Vue 3; para eso, vamos a crear el componente de **SaveComponent.vue**:

src\components\SaveComponent.vue

Ya con esto, recuerda actualizar las referencias en: src\router.js

import { createRouter, createWebHistory } from "vue-router"![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.111.png)

import List from './componets/ListComponent.vue' import Save from **'./componets/SaveComponent.vue'** ///\*\*\*

Ahora, vamos a usar componentes de formularios, en Oruga UI contiene una serie de componentes de formularios como:

- **o-field** para definir los agrupados de los campos.
- **o-input** para los campos de tipo texto.
- **o-input** de tipo type="textarea" para los campos de tipo textarea.
- **o-select** para los campos de selección.

Estos por nombrar algunos, los más comunes.

Vamos a usar los mismos para armar nuestro formulario: src\components\SaveComponent.vue

<o-field label="Title">![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.112.png)

<o-input value=""></o-input> </o-field>

<o-field label="Description">

<o-input type="textarea"></o-input> </o-field>

<o-field label="Content">

<o-input type="textarea"></o-input>

</o-field>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.113.png)

<o-field label="Category">

<o-select placeholder="Select a category">

<option value="">Cate 1</option> </o-select>

</o-field>

<o-field label="Posted">

<o-select placeholder="Select a state">

<option value="yes">Yes</option>

<option value="not">Not</option> </o-select>

</o-field>

<o-button variant="primary">Send</o-button> Con esto, si vamos a:

http://localhost:8080/save

Veremos nuestros componentes:

![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.114.png)

Figura 3-5: Formulario para guardar

<a name="_page71_x28.35_y559.67"></a>Obtener las categorías

Para el listado de categorías, usaremos un listado estático como el siguiente que simula la respuesta devuelta por una Api:

getCategory() { this.categories = [![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.115.png)

{

id:1,

title:'Cate 1' },

{

id:2, title:'Cate 2'![ref19]

},

]

},

Y llamamos al método anterior en el **mounted()**:

mounted() {![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.116.png)

this.getCategory();

},

E iteramos:

<o-field label="Category">![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.117.png)

<o-select v-model="form.category\_id" placeholder="Select a category"> <option v-for="c in categories" v-bind:key="c.id" :value="c.id">

{{ c.title }}

</option>

</o-select>

</o-field>

Y por supuesto, recuerda crear la propiedad para las categorías: categories: []![ref17]

<a name="_page72_x28.35_y396.25"></a>Demo: Crear un post con validaciones

Este apartado también lo puedes tomar de referencia cuando implementes una Api Rest real que aparte de registrar los datos de manera persistente, también pueda ser empleada para validar los datos antes de crear o actualizar los registros y devolver los errores correspondientes; por lo tanto, este apartado debes de tomarlo como referencia al no proveer la API de CRUDCRUD los mecanismos para validar los datos y con esto, devolver los errores de validaciones como el siguiente:

POST - 422![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.035.png)

{

"title": [

"The title has already been taken." ],

"description": [

"The title field is required."

]

}

En este apartado, vamos a realizar una demostración de cómo podríamos configurar el componente para que podamos crear los posts, y mostrar los errores del servidor provistos por las validaciones.

Vamos a comenzar creando los **v-model** para los posts y las propiedades para manejar el mensaje de los errores respectivamente:

form:{![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.118.png)

title:"", description:"", content:"", category\_id:"", posted:"",

},

errors:{

title:"", description:"", content:"", category\_id:"", posted:"",

}

Para el proceso de crear el post (**then()**) y capturar errores (**catch()**) de formularios que es la respuesta de tipo 422 que es el código de estado usualmente empleado cuando existen errores de validación y con esto, poder capturar la excepción al momento de realizar la petición mediante axios:

submit(){![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.119.png)

console.log(this.form) this.cleanErrorsForm()

.post(this.$root.urlCRUDPost, this.form).then(res => {

console.log(res)

}).catch(error =>{

console.log(error.response.data)

if(error.response.data.title)

this.errors.title = error.response.data.title[0]

if(error.response.data.description)

this.errors.description = error.response.data.description[0]

if(error.response.data.category\_id)

this.errors.category\_id = error.response.data.category\_id[0]

if(error.response.data.posted)

this.errors.posted = error.response.data.posted[0]

if(error.response.data.content)

this.errors.content = error.response.data.content[0]

})

},

Los errores pueden estar presentes o no, depende de lo enviado por el usuario, y es por eso los condicionales que verifican si hay errores o no, si hay errores, entonces solamente mostramos el primero y lo establecemos en la propiedad en cuestión.

Limpiar los errores cada vez que hacemos un submit; esto es importante para evitar mostrar el estado anterior del formulario:

cleanErrorsForm(){![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.120.png)

this.errors.title = "" this.errors.description = "" this.errors.category\_id = "" this.errors.content = "" this.errors.posted = ""

},

Y en template para el formulario: <form @submit.prevent="submit">![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.121.png)

<o-field label="Title" :variant="errors.title ? 'danger' : 'primary'" :message="errors.title">

<o-input v-model="form.title" value=""></o-input>

</o-field>

<o-field :variant="errors.description ? 'danger' : 'primary'" :message="errors.description" label="Description">

<o-input v-model="form.description" type="textarea" value=""></o-input> </o-field>

<o-field :variant="errors.content ? 'danger' : 'primary'" :message="errors.content" label="Content">

<o-input v-model="form.content" type="textarea" value=""></o-input>

</o-field>

<o-field :variant="errors.category\_id ? 'danger' : 'primary'" :message="errors.category\_id" label="Category">

<o-select v-model="form.category\_id" placeholder="Select a category">

<option v-for="c in categories" v-bind:key="c.id" :value="c.id">

{{ c.title }}

</option>

</o-select>

</o-field>

<o-field :variant="errors.posted ? 'danger' : 'primary'" :message="errors.posted" label="Posted">

<o-select v-model="form.posted" placeholder="Select a state">

<option value="yes">Yes</option>

<option value="not">Not</option>

</o-select>

</o-field>

<o-button variant="primary" native-type="submit">Send</o-button> </form>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.122.png)

Lo único que hacemos de diferente es, colocar los **v-model** correspondientes y definir el mensaje y clase para cuando existen errores; por ejemplo:

<o-field :variant="errors.posted ? 'danger' : 'primary'" :message="errors.posted" label="Posted">![ref10]

Para eso preguntamos por la condición del mensaje de los errores para cada campo. Con esto, tendremos un componente como el siguiente:

![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.123.jpeg)

Figura 3-6: Formulario con errores de validación

Completamente funcional, y permite crear un post si los datos son correctos y mostrar los errores si existen problemas con la validación de los campos en el servidor.

<a name="_page76_x28.35_y28.35"></a>Editar un registro

Ahora que ya tenemos el proceso de crear, vamos a adaptarlo para que funcione con el proceso de edición; en el archivo de las rutas, colocamos un parámetro opcional que corresponde al id, el id del post que queremos editar, y que nos servirá para la búsqueda del detalle del post:

src\router.js {![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.124.png)

name:'save', path:'/vue/save/**:id?**', component: SaveComponent.vue

},

Ahora, desde el componente de **SaveComponent.vue**, definimos una nueva propiedad:

data() { return { \*\*\*![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.125.png)

post:"" };

},

En la cual registramos el post que queremos editar; cuando montamos el mencionado componente, preguntamos si el id está o no definido:

src\components\SaveComponent.vue

async mounted() { if(this.$route.params.id){![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.011.png)

await this.getPost(); this.initPost();

}

this.getCategory();

},

Recuerda que para acceder a un parámetro que va por la URL, tenemos: this.$route.params.<PARAMETRO>![ref18]

Lógicamente si está definido, entonces estamos en el proceso de editar. En la función de **getPost()**, obtenemos el detalle del post mediante el id:

async getPost() {![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.126.png)

this.post = await this.$axios.get(

this.$root.urlCRUDPost +'/'+ this.$route.params.id );

this.post = this.post.data

69
},![ref3]

En la función de **initPost()** inicializamos el formulario, en otras palabras, los **v-model** de cada una de nuestras propiedades:

initPost(){![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.127.png)

this.form.title = this.post.title this.form.description = this.post.description this.form.content = this.post.content this.form.category\_id = this.post.category\_id this.form.posted = this.post.posted

}

Y en el proceso del submit, puedes preguntar por cualquiera de las formas manejables en este componente para saber si estamos en la fase de editar o crear y con esto, invocar al recurso rest correspondiente:

submit(){ this.cleanErrorsForm()![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.128.png)

**if(this.post == "")**

**return** this.$axios.post(this.$root.urlCRUDPost, this.form).then(res => {

console.log(res)

}).catch(error =>{

**// demo validation error**

})

**// update**

**this.$axios.put(this.$root.urlCRUDPost +'/'+ this.$route.params.id, this.form).then(res**

**=> {**

**console.log(res)**

**}).catch(error =>{**

**// demo validation error console.log(error.response.data)**

**if(error.response.data.title)**

**this.errors.title = error.response.data.title[0]**

**if(error.response.data.description)**

**this.errors.description = error.response.data.description[0]**

**if(error.response.data.category\_id)**

**this.errors.category\_id = error.response.data.category\_id[0]**

**if(error.response.data.posted)**

**this.errors.posted = error.response.data.posted[0]**

**if(error.response.data.content)**

70

**this.errors.content = error.response.data.content[0]![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.129.png)**

**})**

},

<a name="_page78_x28.35_y87.59"></a>Eliminar un registro

El proceso de eliminar es el más sencillo al no requerir de un componente adicional; basta con emplear el de listado y agregar dicha opción.

Vamos a crear una función que reciba un post y lo elimine:

deletePost(row) {![ref12]

this.posts.splice(row.index,1)

// console.log(row);

this.$axios.delete(this.$root.urlCRUDPost +'/' + this.deletePostRow.row.\_id);

},

Importante notar que, no solamente recibimos el post, también recibimos el **row**, es decir, la fila entera; esto es importante ya que, necesitamos el índice de la columna para poder eliminar el post del listado del post de nuestro **array** de posts y hacer también la actualización en el listado; desde el template, creamos el botón:

<o-table-column field="\_id" label="Actions" v-slot="p">![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.130.png)

<router-link

class="mr-3"

:to="{ name: 'save', params: { id: p.row.\_id } }" >Edit</router-link

\>

<o-button

iconLeft="delete"

rounded

size="small" variant="danger"

@click="

deletePostRow = p;

confirmDeleteActive = true; "

\>Delete</o-button

\>

</o-table-column>

Al cual pasamos el **row**, como comentamos antes; si analizamos el **row**, verás que tiene la siguiente estructura:

colindex: 5![ref13]

index: 0

row: Proxy {id: 9, title: 'bpOkH3qSar3xz40HlnDqasasas', \*\*\* }

Y pensando en dicha estructura, la función de borrar el post, tiene la estructura anteriormente señalada.

72

<a name="_page79_x28.35_y28.35"></a>Tailwind.css en el proyecto en Vue con Oruga UI

En este apartado vamos a trabajar con el estilo, el estilo que incluye tanto emplear opciones propias de Oruga UI, como opciones que podemos usar en los componentes de Oruga UI como colores, iconos, redondeado… Y también incluir algún framework CSS para el resto de los detalles, como contenedores, márgenes, alineado, etc; en pocas palabras, cuando queremos definir un estilo personalizado sobre cualquier aspecto que no podamos cubrir con Oruga UI.

Tailwind es un framework web excelente que se enfoca en proporcionar clases de utilidad y que ayudan a diseñar páginas web completamente personalizables y es el framework web que vamos a usar en este proyecto para este capítulo.

Instalamos Tailwind y generamos los archivos de configuración:

$ npm install -D tailwindcss postcss autoprefixer $ npx tailwindcss init -p![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.131.png)

Con esto, generará los siguientes archivos de configuración de Tailwind y PostCSS:

tailwind.config.js postcss.config.js![ref20]

Y ahora, debemos de especificarle a Tailwind, que archivos son los que van a contener las clases de Tailwind, deben ser al menos los de la extensión .vue, pero podemos definir otros relacionados:

tailwind.config.js

module.exports = {![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.133.png)

content: [

"./index.html",

"./src/\*\*/\*.{vue,js,ts,jsx,tsx}", ],

theme: {

extend: {},

},

plugins: [],

}

Y creamos un archivo CSS con las dependencias: src\css\main.css

@tailwind base; @tailwind components; @tailwind utilities;![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.134.png)

.my-table{

@apply table-fixed max-w-lg mt-2

74
}![ref19]

.o-input\_\_wrapper, .o-ctrl-sel, input[type='text'], textarea, select{

@apply w-full

}

Agregamos algunas clases para dar estilo a la tabla y que los campos de formularios y sus envoltorios ocupen todo el espacio disponible.

E importamos la hoja desde la instancia principal de Vue: src\main.js

import { createApp } from 'vue'![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.135.png)

**import "./css/main.css"**

import axios from "axios"

// \*\*\*

Es importante colocar la importación de Tailwind CSS antes de la de Oruga UI; ya que, si la colocas después; entonces Tailwind.CSS sobrescribirá todo el CSS de estilo de Oruga UI, cosa que no queremos.

<a name="_page80_x28.35_y366.10"></a>Container

Ahora, ya con Tailwind.css configurado en nuestro proyecto en Laravel con Vue, vamos a aplicar un **container** para que el contenido que tenemos no se vea tan estirado:

src\App.vue

<template>![ref21]

<div class='container m-auto'>

<router-view></router-view> </div>

</template>

<a name="_page80_x28.35_y551.40"></a>Cambios varios en el componente de listado

En este apartado, vamos a colocar algunos iconos y márgenes para el apartado de acciones en la tabla; aparte de esto, cambiamos el enlace en la fase de creación por un botón y hacemos la navegación de manera programática:

src\components\ListComponent.vue

<template>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.003.png)

<div>

**<h1>List</h1>**

**<o-button iconLeft="plus" @click="$router.push({ name: 'save' })" >Create</o-button>**

76

**<div class="mb-5" ></div>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.137.png)**

<o-table

:loading="isLoading"

:data="posts.current\_page && posts.length == 0 ? [] : posts" >

//\*\*\*

<o-table-column field="\_id" label="Actions" v-slot="p">

<router-link **class="mr-3"** :to="{ name: 'save', params: { id: p.row.\_id } }"

\>Edit</router-link

\>

<o-button **iconLeft="delete" rounded size="small" variant="danger"** @click="deletePost(p)"

\>Delete</o-button

\>

</o-table-column>

</o-table>

// \*\*\*

También recuerda definir tu estilo global para los H1 que usaremos para definir los títulos de cada pantalla: src\css\main.css

//\*\*\*![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.138.png)

@import 'tailwindcss/utilities'; h1{

@apply text-3xl text-center my-5 }

Y queda de la siguiente manera:

![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.139.jpeg)

Figura 3-7: Listado de publicaciones con acciones

77

<a name="_page82_x28.35_y28.34"></a>Cambios varios en el componente de guardado

En este apartado, vamos a colocar los campos de formulario en un sistema de grid, para evitar que el contenido se vea todo estirado:

<template>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.140.png)

**<h1 v-if="post">Update Post <span class="font-bold">{{post.title}}</span></h1> <h1 v-else>Create Post</h1>**

<form @submit.prevent="submit">

**<div class="grid grid-cols-2 gap-3">**

**<div class="col-span-2">**

<o-field

label="Title"

:variant="errors.title ? 'danger' : 'primary'" :message="errors.title"

\>

<o-input v-model="form.title" value=""></o-input> </o-field>

**</div>**

<o-field

:variant="errors.description ? 'danger' : 'primary'" //\*\*\*

**</div>**

<o-button variant="primary" native-type="submit">Send</o-button> </form>

</template>

Y queda de la siguiente manera:

![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.141.jpeg)

Figura 3-8: Formulario organizado en grids

<a name="_page83_x28.35_y312.17"></a>Mensaje de confirmación para eliminar

Para evitar borrar registros por errores, vamos a colocar un diálogo de confirmación; que en Oruga UI ya tenemos un componente de modal llamado **o-modal**; el mismo implementa un **v-model:active** con el cual indicamos si queremos ver el modal (true) o no (false), por lo demás, el **o-modal** de Oruga UI no es más que un contenedor en el cual colocamos cualquier HTML:

src\componets\ListComponent.vue

<o-modal v-model:active="confirmDeleteActive">![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.142.png)

<div class="p-4">

<p>Are you sure you want to delete the selected record?</p> </div>

<div class="flex flex-row-reverse gap-2 bg-gray-100 p-3">

<o-button variant="danger" @click="deletePost()">Delete</o-button>

<o-button @click="confirmDeleteActive = false">Cancel</o-button> </div>

</o-modal>

<h1>List</h1> \*\*\*

Como puede ser, el modal que construimos consta de un mensaje de confirmación, y los botones de acción:

- **Cancelar**, para cerrar el modal.
- **Eliminar**, para borrar el registro; para esto, vamos a usar la misma función de eliminar que implementamos anteriormente, pero removiendo el parámetro de la función que ahora debemos referenciar desde una propiedad de Vue.

Para la acción de eliminar de la tabla, al ya no borrar el registro de manera directa, vamos a modificarlo para que haga dos pasos:

1. Abra el modal mediante el **v-model** que abre al modal.
1. Establece la propiedad que vamos a usar para eliminar el registro seleccionado.

   <o-table-column field="\_id" label="Acciones" v-slot="p">![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.143.png)

<router-link

class="mr-3"

:to="{ name: 'save', params: { id: p.row.\_id } }" >Edit</router-link

\>

<o-button

iconLeft="delete"

rounded

size="small" variant="danger"

**@click="**

**deletePostRow = p;**

**confirmDeleteActive = true; "**

\>Delete</o-button

\>

</o-table-column>

</o-table>

La declaración de las nuevas propiedades para manejar la visualización del modal y para la referencia al registro a eliminar respectivamente:

data() {![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.144.png)

return {

//\*\*\*

**confirmDeleteActive: false, deletePostRow: "",**

};

Finalmente, adaptamos la función de eliminar, para eliminar el registro seleccionado:

deletePost() {![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.145.png)

**this.confirmDeleteActive = false;** this.posts.splice(**this.deletePostRow.index**, 1); this.$axios.delete(this.$root.urlCRUDPost +'/' + **this.deletePostRow.row.\_id**);

},

Y tenemos:

![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.146.png)

Figura 3-9: Dialog para eliminar

Este es un diseño simple y mínimo, pero puedes adaptarlo para que tenga cabeceras, mensajes más completos, referencia al registro que quieres eliminar o lo que consideres.

<a name="_page85_x28.35_y211.80"></a>Mensaje de acción realizada

Otro detalle que falta para nuestra aplicación, viene siendo el de mostrar un mensaje a la acción realizada; por ejemplo, cuando creamos, actualizamos o eliminamos un registro, no existe de momento algún mensaje que indica al usuario que dicha acción se llevó a cabo; en Oruga UI tenemos dos formas de hacer esto; mediante notificaciones tipo bloques:

![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.147.jpeg)

Figura 3-10: Componente de notificación

O por los famosos toasts:

![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.148.png)

Figura 3-11: Componente de toast <https://oruga.io/components/Notification.html#examples>

En ese libro vamos a usar los últimos; los cuales, para usarlos, tenemos que usar la función de: **this.$oruga.notification.open()![ref3]**

E indicamos mediante un objeto, las opciones de dicha notificación; podemos personalizar bastantes aspectos sobre la misma como colores, posiciones, mensajes, duración, efecto, entre otros; entre los principales tenemos:

- **message**: Para definir el mensaje, que puede ser texto o HTML.
- **position**: Para indicar la posición: top-right, top, top-left, bottom-right, bottom, bottom-left.
- **variant**: Para indicar el color.
- **closable**: Para indicar si quieres que pueda cerrarse con un click.

Finalmente, con esto en mente, vamos a notificar dichas acciones en el listado: src\componets\ListComponent.vue

deletePost() {![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.149.png)

this.confirmDeleteActive = false; this.posts.splice(this.deletePostRow.index, 1); this.$axios.delete(this.$root.urlCRUDPost +'/' + this.deletePostRow.row.\_id); **this.$oruga.notification.open({**

**message: "Registro eliminado",**

**position: "bottom-right",**

**variant: "danger",**

**duration: 4000,**

**closable: true,**

**});**

},

Para actualizar y crear: src\components\SaveComponent.vue

submit() {![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.150.png)

this.cleanErrorsForm();

if (this.post == "")

**return this.$axios**

**.post(this.$root.urlCRUDPost, this.form)**

**.then((res) => {**

**this.$oruga.notification.open({**

**message: "Registration processed successfully",**

**position: "bottom-right",**

**duration: 4000,**

**closable: true,**

**});**

**})**

.catch((error) => {

//\*\*\*

// actualizar

**this.$axios**

**.put(this.$root.urlCRUDPost +'/'+ this.$route.params.id, this.form) .then((res) => {**

**this.$oruga.notification.open({**

**message: "Registration processed successfully",**

**position: "bottom-right",**

**duration: 4000,![ref22]**

**closable: true, });**

**})**

.catch((error) => {

//\*\*\* },

Y con esto, al realizar una de las acciones anteriormente mencionadas tendremos el resultado de la figura 3-11.

<a name="_page87_x28.35_y161.13"></a>Demo: Upload de archivos

Otra de las carencias que tenemos en CRUDCRUD es que no podemos realizar uploads de archivos, por lo tanto, la siguiente implementación también debes de tomarla como referencia al momento de que emplees una Api Rest real.

En este apartado, vamos a conocer como podemos implementar la carga de archivos usando como backend a Laravel y en el cliente un componente de Oruga UI y Vue 3.

<a name="_page87_x28.35_y293.56"></a>Recurso Rest

El siguiente código, corresponde a un proceso de upload de archivos en Laravel, como comentamos antes, lo puedes tomar de referencia para replicar en otras tecnologías en los cuales quieres crear tu Api Rest:

public function upload(Request $request, Post $post)![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.151.png)

{

$data["image"] = $filename = time() . "." . $request["image"]->extension(); $request->image->move(public\_path("image/otro"), $filename);

$post->update($data);

return response()->json($post);

}

La ruta luce como:

routes\api.php

Route::post('post/upload/{post}', [PostController::class, 'upload']); ![ref18]Y probamos por Postman:

![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.152.jpeg)

Figura 3-12: Upload de ejemplo Es importante colocar el campo de tipo file y subir el archivo.

<a name="_page88_x28.35_y389.27"></a>Vue 3 y componente upload en Oruga UI

Ahora, vamos a usar el componente de Oruga UI para la carga de archivos:

**<o-upload v-model="file">![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.153.png)**

**\*\*\***

**</o-upload>**

El cual, como puedes ver, define un v-model para establecer el archivo seleccionado. Nuestro código quedará de la siguiente manera; para el template:

<template> //\*\*\*![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.154.png)

<o-select v-model="form.posted" placeholder="Seleccione un estado">

<option value="yes">Yes</option>

<option value="not">Not</option>

</o-select>

</o-field>

**<div class="flex gap-2" v-if="post">**

**<o-upload v-model="file">**

**<o-button tag="a" variant="primary">**

**<o-icon icon="upload"></o-icon>![ref23]**

**<span>Click to upload</span> </o-button>**

**</o-upload>**

**<o-button icon-left="upload" @click="upload"> Upload </o-button> </div>**

**</div>**

**<br />**

//\*\*\*

<o-button variant="primary" native-type="submit">Send</o-button> </template>

Su propiedad:

data() {![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.156.png)

return {

categories: [], //\*\*\*

**file: null,**

};

},

Y la función de **upload()**, la cual será una petición por axios, estableciendo la cabecera de **multipart/form-data** para indicar que podemos cargar archivos:

methods: {![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.157.png)

//\*\*\*

upload() {

//return console.log(this.file)

const formData = new FormData() formData.append("image",this.file)

this.$axios

.post("<URL>/upload" + this.post.id, formData, {

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

![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.158.png)

Figura 3-13: Opciones para cargar archivo

<a name="_page90_x28.35_y145.52"></a>Manejo de errores de formulario

Para mostrar los errores que puedan ocurrir en el servidor (por ejemplo, un tipo de archivo no soportado), en el controlador, vamos a colocar las validaciones locales a la función:

public function upload(Request $request, Post $post)![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.159.png)

{

**$request->validate([**

**'image' => "required|mimes:jpeg,png,gif|max:10240"**

**]);** // \*\*\*

}

Vamos a embeber el **o-upload** en un **o-field** para poder manejar el mensaje de error al igual que hicimos con los campos de formulario anteriores:

**<o-field :message="fileError"> ![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.160.png)**<o-upload v-model="file"> \*\*\*\*

</o-upload>

**</o-field>**

Creamos la propiedad:

fileError: "",![ref1]

Y definimos en la función de **upload()**, en el catch, el mapeo de los errores:

upload() {![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.161.png)

//return console.log(this.file)

**this.fileError = ""**

const formData = new FormData(); formData.append("image", this.file);

this.$axios

.post("<URL>/upload" + this.post.id, formData, {

headers: {

"Content-Type": "multipart/form-data",

},![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.035.png)

})

.then((res) => {

console.log(res);

})

**.catch((error) => {**

**this.fileError = error.response.data.message; });**

},

Si colocas un tipo de archivo no soportado, verás un error de validación. Si quieres que el texto aparezca en rojo, creamos el siguiente estilo:

.o-field\_\_message{![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.162.png)

@apply text-red-800

}

O colocas la variante:

<o-field :variant="file ? 'danger' : 'primary'"![ref3]

<a name="_page91_x28.35_y337.27"></a>Opcional: Upload de archivos vía Drag and Drop

La carga de archivos en base al Drag and Drop, arrastrando el archivo a un contenedor es una práctica muy requerida hoy en día; vamos a conocer como podemos emplear este componente de Oruga UI.

Este tipo de carga de archivos es particularmente útil cuando queremos cargar varos archivos (carga múltiple de archivos) que no se ajusta a nuestras necesidades ya que, recuerda que un post solamente puede tener una única imagen; sin embargo, desde la función controladora para el upload, podemos usar sin problemas el upload de tipo múltiple.

Definimos una propiedad de tipo **array**, ya que, podemos tener múltiples archivos como explicamos anteriormente:

filesDaD: [],![ref24]

En cuanto al contenedor, es similar al anterior, ya que, seguimos empleando el **o-upload** y definimos un par de atributos para la carga múltiple (**multiple**) y habilitar el drag and drop:

<div class="flex gap-2" v-if="post">![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.164.png)

<o-field :message="fileError">

<o-upload **v-model="filesDaD" multiple drag-drop**>

**<section>**

**<o-icon icon="upload"></o-icon>**

**<span>Drag and Drop to upload</span> </section>**

</o-upload>

</o-field>

<span v-for="(file, index) in filesDaD" :key="index">![ref9]

{{ file.name }}

</span>

</div>

Por lo demás, definimos un **SECTION** como elemento contenedor, pero puedes adaptar este contenedor con el diseño que quieras.

Para ver los archivos que vamos cargando, podemos iterar los mismos que se encuentran almacenados en la propiedad **filesDaD**:

<span v-for="(file, index) in filesDaD" :key="index">![ref6]

{{ file.name }}

</span>

Para observar los cambios y subir un archivo al servidor cada vez que el usuario arroja uno sobre el contenedor, vamos a usar exactamente el mismo código de la función que definimos anteriormente, pero, pasando como parámetro del **FormData** la referencia al archivo en vez de la propiedad **file**:

watch: {![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.165.png)

filesDaD: {

handler(val) {

//return console.log(val[val.length - 1]); this.fileError = ""

const formData = new FormData(); formData.append("image", val[val.length - 1]); this.$axios

.post("<URL>/upload" + this.post.id, formData, {

headers: {

"Content-Type": "multipart/form-data",

},

})

.then((res) => {

console.log(res);

})

.catch((error) => {

this.fileError = error.response.data.message; });

},

deep: true,

},

},

Finalmente, tendremos:

![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.166.png)

Figura 3-14: Componente de Drag and Drop

Código fuente del capítulo: <https://github.com/libredesarrollo/curso-libro-vueoruga>

**Capítulo 4: Consumir una Api Rest tipo CRUD con <a name="_page94_x28.35_y61.47"></a>Vue y Naive UI**

En este capítulo vamos a crear otra aplicación en Vue tipo CRUD consumiendo la API de CRUDCRUD pero empleando Naive UI en lugar de Oruga UI como framework web basado en componentes; este proyecto que vamos a llevar a cabo en este capítulo es una variación del creando en mi libro de Django:

<https://www.desarrollolibre.net/libros/primeros-pasos-django>

En el cual, creamos una Api Rest personalizada en Django que luego consumimos con Vue.

La temática de esta aplicación será similar a la anterior, pero, un poco más compleja, tendremos categorías y tipos que pueden ser asignados a una entidad que llamados elementos:

{![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.167.png)

class Element(models.Model):

title,

slug,

description,

price,

category,

type,

}

Los elementos pueden ser cualquier cosa como una publicación o producto o cualquier otro que quieras tipificar mediante el tipo/type.

<a name="_page94_x28.35_y437.55"></a>Preparar el entorno

Vamos a generar un proyecto empleando la Vue CLI como mostramos en el capítulo 1 llamado **vuenaiveui**.

<a name="_page94_x28.35_y497.26"></a>Vue Router

Vamos a crear una aplicación que va a necesitar de varias pantallas, por lo tanto, vamos a necesitar un mecanismo para poder navegar fácilmente entre estas distintas pantallas o páginas; para eso, podemos emplear Vue Router que en resumen nos ofrece un sencillo esquema de navegación entre páginas tipo SPA.

<a name="_page94_x28.35_y586.05"></a>Instalación

Para instalar el paquete a nivel del proyecto, ejecutamos: $ npm install vue-router@4![ref4]

<a name="_page94_x28.35_y668.47"></a>Configuración del proyecto

Creamos un archivo con las rutas:

src\router.js

import { createRouter, createWebHistory } from "vue-router"![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.168.png)

import List from './components/ListComponent.vue' import Save from './components/SaveComponent.vue'

const routes = [

{

name:'list',

path:'/',

component: List },

{

name:'save', path:'/save/:id?', component: Save

},

]

const router = createRouter({

history: createWebHistory(), routes: routes

})

export default router

Y lo cargamos en el archivo principal de Vue (donde creamos la instancia principal de Vue). Lo usamos desde el **main:js**:

import { createApp } from 'vue'![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.169.png)

import App from './App.vue' import router from "./router"

const app = createApp(App) app.use(router)

app.mount('#app')

Y creamos el contenedor donde va a existir la web SPA construida con Vue Router y Vue:

<template>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.170.png)

<h1>App Vue</h1>

<router-view></router-view> </template>

<a name="_page96_x28.35_y28.34"></a>Creación de componentes

Ahora, creamos los componentes que van a fungir como nuestras páginas navegables: src\pages\ListCategory.vue

<template>![ref5]

<h1>List Categories</h1> </template>

Y src\pages\ListType.vue

<template>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.171.png)

<h1>List Types</h1> </template>

<a name="_page96_x28.35_y265.55"></a>**Configurar axios**

Vamos a configurar axios como paquete para realizar peticiones HTTP a la Rest Api de CRUD CRUD; lo instalamos con:

$ npm install axios![ref3]

Y a nivel del archivo principal de Vue, cargamos a axios como una propiedad global de Vue: import { createApp } from 'vue'![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.172.png)

import axios from "axios"

import App from './App.vue' import router from "./router"

const app = createApp(App)

app.config.globalProperties.$axios = axios window.axios = axios

app.use(router) app.mount('#app')

<a name="_page96_x28.35_y623.85"></a>Fase 1: Listados

En esta primera fase del proyecto en Vue, vamos a crear el proyecto, estructura base, instalar las dependencias que vamos a usar y crear un sencillo listado de tipos, categorías y elementos.

Puedes crear alguna data de prueba en la API de CRUD CRUD para poder probar los listados.

<a name="_page97_x28.35_y28.34"></a>Consumir la Rest Api mediante axios (primeras pruebas)

Vamos a realizar algunas pruebas de conexión; en las cuales, vamos a consumir las categorías y tipos desde la rest api con el método **mounted()** de Vue desde sus respectivos componentes e iterarlos en el template:

src\pages\ListCategory.vue

<template>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.173.png)

<h1>List Categories</h1>

<router-link :to="{ name: 'list-type' }">Types</router-link>

<div v-for="c in categories" :key="c.id">

<p>{{ c.title }}</p>

</div>

</template>

<script>

export default {

data(){

return {

categories:[]

}

},

mounted(){

this.$axios.get(this.$root.urlCRUDCategory) .then((res)=>{

this.categories = res.data

})

}

}

</script>

src\pages\ListType.vue

<template>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.174.png)

<h1>List Types</h1>

<router-link :to="{ name: 'list-category' }">Categories</router-link>

<div v-for="c in types" :key="c.id">

<p>{{ c.title }}</p>

</div>

</template> <script>

export default {

data(){

return {![ref23]

types:[]

}

},

mounted(){

this.$axios.get(this.$root.urlCRUDType) .then((res)=>{

this.types = res.data

})

}

}

</script>

**Explicación del código anterior**

El código es sencillo, desde el **mount()**, hacemos la petición tipo GET con axios para consumir sus respectivos listados que registramos en una propiedad que luego iteramos mediante un **v-for** en el template.

A su vez, definimos un método **router-link** para navegar entre las rutas con nombre que definimos en la definición de las rutas; finalmente, deberías de ver algo como:

![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.175.png)

Figura 4-1: Listado inicial de categorías

<a name="_page98_x28.35_y596.53"></a>Naive UI, para los componentes de interfaz gráfica

Vamos a emplear un paquete que provee de componentes de interfaz gráfica; la cual, tal cual indica la web oficial:

<https://www.naiveui.com/>

Contiene más de 80 componentes que podemos usar, entre menús, botones, tablas, layouts, campos de formularios, paso por paso y un largo etc.

<a name="_page99_x28.35_y28.34"></a>Instalar

Para usar este paquete, tenemos que instalarlo junto con sus fuentes tipográficas; para eso:

$ npm i -D naive-ui $ npm i -D vfonts![ref10]

<a name="_page99_x28.35_y109.58"></a>Configurar

Su configuración es de lo más simple, y basta con importar el paquete instalado anteriormente y usar la función de **use()** de Vue 3.

src\main.js

import naive from 'naive-ui' //\*\*\*![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.105.png)

app.use(naive)

<a name="_page99_x28.35_y265.25"></a>Configurar tabla en los listados

Ahora, vamos a usar el primer componente, el de tabla, para ello tenemos que hacer uso del componente **n-table**:

src\pages\ListCategory.vue

<template>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.176.png)

<h1>List Categories</h1>

<router-link :to="{ name: 'list-type' }">Types</router-link>

<n-table :bordered="true" :single-line="false">

<thead>

<tr>

<th>Title</th>

<th>Actions</th>

</tr>

</thead>

<tbody>

<tr v-for="c in categories" :key="c.id">

<td>{{ c.title }}</td>

<td>

\_\_

</td>

</tr>

</tbody>

</n-table>

</template>

El mismo listado de tablas lo tienes que aplicar para el componente de los tipos; finalmente, tendremos algo como el siguiente diseño:

![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.177.png)

Figura 4-2: Tabla en NaiveUI

<a name="_page100_x28.35_y246.02"></a>Container

Para evitar que el contenido se vea todo estirado, vamos a usar el componente de **n-space**:

src\App.vue

<n-space justify="center">![ref5]

<router-view></router-view> </n-space>

**Explicación del código anterior**

Con el **n-space** podemos ajustar el contenido de diversas maneras, si lo quieres alinear a la derecha, centro o izquierda, entre otros tipos de alineados.

<a name="_page100_x28.35_y428.69"></a>Layout

El componente de layout nos permite armar la estructura base de la web, como sección de cabecera, footer, contenido, sidebar, etc; vamos a usar un diseño como el siguiente:

![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.178.png)

Figura 4-3: Layout básico

Y para eso, usamos el componente **n-layout**: src\App.vue

<n-space justify="center"> <n-layout has-sider> <n-layout-sider>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.179.png)

--Menu </n-layout-sider>

<n-layout>

<n-layout-header> Title </n-layout-header> <n-layout-content>

<router-view></router-view>

</n-layout-content>

<!-- <n-layout-footer>Footer</n-layout-footer> --> </n-layout>

</n-layout>

</n-space>

**Explicación del código anterior**

El componente de **n-layout** permite estructurar el contenido que queramos mostrar en la web; definir elementos como la cabecera, contenido y pie de página son fácilmente estructurados con este componente; en el código anterior también puedes ver que colocamos un layout dentro de otro layout, el primer layout tiene el propósito de establecer el sidebar y el segundo el contenido contenido.

Y un estilo provisional para ver los contenedores:

src\App.vue

<style>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.180.png)

.n-layout-header,

.n-layout-footer {

background: rgba(128, 128, 128, 0.2); padding: 24px;

}

.n-layout-sider {

background: rgba(128, 128, 128, 0.3); }

.n-layout-content {

background: rgba(128, 128, 128, 0.4); }

</style>

Tendremos:

![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.181.png)

Figura 4-4: Layout básico en la aplicación

<a name="_page102_x28.35_y537.79"></a>Menú

Opciones para menús tenemos muchos, aunque en la parte del script son todos similares; vamos a implementar una opción sencilla como la siguiente:

src\App.vue

//\*\*\*![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.127.png)

<n-layout-sider>

<n-menu :options="options"> </n-menu> </n-layout-sider>

//\*\*\*

data() {

return {

options: [![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.182.png)

{

label: "Categories", key: "1 parent", children: [

{

label: "List",

key: "list c 1", },

{

label: "Cate 1", key: "c 1",

}, {

label: "Cate 1", key: "c 2",

},

],

}, {

label: "Types", key: "2 parent", children: [

{

label: "List",

key: "list t 1", },

{

label: "Type 1", key: "t 1",

}, {

label: "Type 1", key: "t 2",

},

],

},

],

};

},

**Explicación del código anterior**

El componente de **n-menu** recibe como parámetro las opciones del menú; al ser el menú que escogimos de dos niveles, tenemos en cada componente un subnivel:

- El label es para colocar el texto a mostrar en el menú.
- La key es empleada de manera interna para identificar cada ítem del menú.
- El children se usa para crear un menú multinivel.

Con esto tendremos:

![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.183.png)

Figura 4-5: Layout con menú

<a name="_page104_x28.35_y282.92"></a>Menú: Navegación mediante Router Link

De momento el menú que tenemos no es muy funcional, ya que, no podemos hacer nada al darle click a los ítems; para esto, tenemos varias soluciones, desde emplear enlaces, hasta emplear el **v-model:value="selectedKey"** para guardar referencia al ítem presionado por un click.

Ya que estamos usando Vue Router, la opción que nos interesa es la de poder establecer un enlace de navegación con el **router-link**; para ello vamos a usar el siguiente código:

src\App.vue

import { h } from "vue";![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.184.png)

import { RouterLink } from "vue-router"; //\*\*\*

{

label: () =>

h(

RouterLink,

{

to: {

name: "list-category",

},

},

{ default: () => "List" }

),

key: "list cate",

},

\*\*\*

],

}, {

label: "Types",![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.185.png)

key: "2 parent",

children: [

{

label: () =>

h(

RouterLink,

{

to: {

name: "list-type", },

},

{ default: () => "List" } ),

key: "list type",

},

\*\*\*

**Explicación del código anterior**

El componente de menú no implementa directamente un **router-link**, por lo tanto, tenemos que usar la función genérica para poder implementar el enlace de navegación en la opción de **label**.

Para poder definir el router-link desde la sección de script, tenemos que importarlo desde el paquete de Vue Router y también es necesario la función **h()** de Vue, que permite renderizar tanto componentes de Vue como HTML en general:

import { h } from 'vue'![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.186.png)

const vnode = h(

'div', // type

{ id: 'foo', class: 'bar' }, // props [

/\* children \*/

]

)

Con la función **h()** de Vue tenemos que indicar como parámetros:

1. Componente o etiqueta HTML que queremos renderizar.
1. Atributos o **props** del componente o etiqueta HTML.
1. Opciones por defecto, en este caso, el label.

Con esto, tendremos que el ítem llamado “List” tiene implementado un **router-link**. <a name="_page105_x28.35_y646.20"></a>Menú: Enlaces dinámicos

En los elementos hijos del menú (los que van en el children), nos va a interesar que aparezcan las opciones reales que tenemos, es decir, las categorías y tipos que tenemos registrados en la base de datos; para esto, vamos a hacer uso de la reactividad que cuenta Vue y vamos a definir por defecto la propiedad de **options** como vacía:

data() {![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.187.png)

return {

options: [], };

},

Luego, creamos un par de funciones para obtener el detalle de los tipos y categorías (las mismas que se usan en los componentes de tipos y categorías) y creamos un enlace por cada categoría o tipo que exista en la base de datos:

src\components\MenuSidebar.vue

mounted() {![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.188.png)

this.categories();

this.types();

},

methods: {

types() {

this.$axios

.get(this.$root.urlCRUDCategory) .then((res) => {

const optionType = [

{

label: () =>

h(

RouterLink,

{

to: {

name: "list-type", },

},

{ default: () => "List" } ),

key: "list type",

},

];

res.data.forEach((t) => {

optionType.push({

label: t.title,

key: "t " + t.\_id,

});

});

this.options.push({

label: "Types",

key: "2 parent",

children: optionType,

});

});![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.189.png)

},

categories() {

this.$axios

.get(this.$root.urlCRUDType) .then((res) => {

const optionCate = [

{

label: () =>

h(

RouterLink,

{

to: {

name: "list-category", },

},

{ default: () => "List" } ),

key: "list cate",

},

];

res.data.==.forEach((c) => {

optionCate.push({

label: c.title,

key: "c " + c.id,

});

});

this.options.push({

label: "Categories",

key: "1 parent",

children: optionCate,

});

});

},

},

A nivel visual, tenemos la misma estructura que la presentada anteriormente, lo único que cambia ahora es la fuente de datos.

<a name="_page107_x28.35_y590.41"></a>Menú: Reutilizable

Para tener la aplicación en Vue organizada, vamos a mover el menú del componente **App.vue** a un nuevo componente que sea exclusivo para el menú; para eso, debes de copiar todo el bloque de script del componente **App.vue** en este nuevo componente y el **n-menu**.

src\components\MenuSidebar.vue <template>![ref14]

<n-menu :options="options"> </n-menu> </template>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.190.png)

<script>

import { h } from "vue";

import { RouterLink } from "vue-router"; export default {

name: "App",

data() {

return {

options: [],

};

},

mounted() {

this.categories();

this.types();

},

methods: {

// \*\*\*

},

};

</script>

Y en el **App.vue**, importamos el componente anterior:

src\App.vue

<n-layout-sider>**<MenuSidebar />** </n-layout-sider>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.191.png)

<n-layout>

<n-layout-header> Title </n-layout-header> <n-layout-content class="p-2">

<router-view></router-view> </n-layout-content>

</n-layout>

</n-layout>

</n-space>

</template>

**<script>**

**import MenuSidebar from "@/components/MenuSidebar";**

**export default { components: {**

**MenuSidebar, },**

**};**

**</script>**

Debes de tener exactamente el mismo comportamiento que tenías antes, pero ahora tenemos el menú en un archivo aparte.

<a name="_page109_x28.35_y71.98"></a>Header con menú

Como parte de hacer más pruebas y usar más componentes, vamos a crear un header para la aplicación con un layout de tipo header y el mismo menú que definimos anteriormente, pero orientado de manera horizontal:

src\components\MenuHeader.vue

<template>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.192.png)

<n-menu :options="options" mode="horizontal"> </n-menu> </template>

<script>

//\*\*\* </script>

Importante notar que, es exactamente el mismo JavaScript que teníamos en el anterior componente de menú, así que, lo puedes replicar; para el **App.vue**, lo importamos:

<n-layout class="mb-5">![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.193.png)

<n-layout-header>

AppVue

**<MenuHeader />**

</n-layout-header>

</n-layout>

\*\*\*

<script>

import MenuSidebar from "@/components/MenuSidebar"; **import MenuHeader from "@/components/MenuHeader";** export default {

components: {

MenuSidebar,

**MenuHeader,**

},

};

</script>

Y quedaría, algo como lo siguiente:

![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.194.png)

Figura 4-6: Menú en el header

<a name="_page110_x28.35_y265.67"></a>Instalar y configurar Tailwind CSS

Para poder adaptar esos pequeños cambios de estilo, de espaciado, colores, subrayados, entre otros, vamos a instalar Tailwind junto con NaiveUI; importante mencionar que, NaiveUI no está pensado para integrar este tipo de frameworks como Tailwind.CSS así que, esto es netamente experimental y si te gusta el resultado, puedes usarlo perfectamente para tus aplicaciones que usen también NaiveUI.

Instalamos Tailwind y generamos los archivos de configuración:

$ npm install -D tailwindcss postcss autoprefixer $ npx tailwindcss init -p![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.195.png)

Con esto, generará los siguientes archivos de configuración de Tailwind y PostCSS:

tailwind.config.js postcss.config.js![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.196.png)

Y ahora, debemos de especificarle a Tailwind, que archivos son los que van a contener las clases de Tailwind, deben ser al menos los de la extensión .vue, pero podemos definir otros relacionados:

tailwind.config.js

module.exports = {![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.197.png)

content: [

"./index.html",

"./src/\*\*/\*.{vue,js,ts,jsx,tsx}", ],

theme: {

extend: {},

},

plugins: [],

}

Y creamos un archivo CSS con las dependencias: src\css\main.css

@tailwind base; @tailwind components; @tailwind utilities;![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.198.png)

.my-table{

@apply table-fixed max-w-lg mt-2 }

Y lo importamos desde nuestro Vue: src\main.js

import { createApp } from 'vue' ![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.199.png)**import "./css/main.css"**

import axios from "axios" import naive from 'naive-ui'

// \*\*\*

Ya con esto tenemos Tailwind CSS listo en nuestro proyecto para empezar a configurar en nuestra aplicación.

<a name="_page111_x28.35_y425.08"></a>Arreglar pequeños detalles

Ya con Tailwind, vamos a aprovechar a mejorar algunos aspectos; el primero es con la tabla que usamos para el listado, vamos a definir un tamaño mayor por defecto y un margen para el top:

src\css\main.css

.my-table{![ref5]

@apply table-fixed max-w-lg mt-2 }

Y en las tablas, se emplea la clase anterior:

src\pages\ListType.vue src\pages\ListCategory.vue

**<n-button type="primary">![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.200.png)**

<router-link :to="{ name: '...' }">...</router-link> **</n-button>**

<n-table :bordered="true" :single-line="false" **class="my-table"**>

También se van a embeber los enlaces de tipo **RouterLink** en la tabla con un botón de NaiveUI.

Y ya para terminar estos detalles, vamos a colocar algo de PADDING en el contenedor del layout para evitar que todo aparezca tan junto:

src\App.vue

<n-layout-content **class="p-2"**>![ref25]

<router-view></router-view> </n-layout-content>

<a name="_page112_x28.35_y189.16"></a>Listado de elementos

Ahora es turno de crear el listado de elementos, el cual tiene la misma organización que los listados de tipos y categorías:

src\pages\ListElement.vue

<template>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.202.png)

<!-- <n-button type="primary">

<router-link :to="{ name: 'list-type' }">Types</router-link> </n-button> -->

<n-table :bordered="true" :single-line="false" class="my-table">

<thead>

<tr>

<th>Id</th>

<th>Title</th>

<th>Price</th>

<th>Date</th>

<th>Actions</th>

</tr>

</thead>

<tbody>

<tr v-for="e in elements" :key="e.\_id">

<td>{{ e.\_id }}</td>

<td>{{ e.title }}</td>

<td>{{ e.price }}</td>

<td>

<n-button type="primary">

\_\_

</n-button>

</td>

</tr>

</tbody>

</n-table>

</template>

<script>

export default {![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.203.png)

data() {

return {

elements: [],

};

},

mounted() {

let url = this.$root.urlCRUDElement; this.$axios.get(url).then((res) => {

this.elements = res.data;

});

},

};

</script>

<a name="_page113_x28.35_y235.71"></a>Rutas agrupadas

Vamos a aprovechar a definir la ruta para el listado de elementos y también de agrupar las rutas comunes en un solo tipo de ruta:

const routes = [![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.204.png)

{

path: '/list',

component: BasePage, children: [

{

name: 'list-element',

path: 'element/:id?',

component: ListElement },

{

name: 'list-category', path: 'category', component: ListCategory

}, {

name: 'list-type', path: 'type', component: ListType

}

]

}

]

Por lo tanto, ahora las rutas van a cambiar de lo que ya manejamos antes: http://localhost:8080/element/

A algo como esto:

http://localhost:8080/list/element/

Definimos el componente base para las rutas agrupadas:

src\pages\BasePage.vue

<template>![ref25]

<router-view></router-view> </template>

Y con esto, el resto de las rutas deberían de seguir funcionando correctamente junto con la de listado de elementos:

![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.205.png)

Figura 4-7: Listado de elementos

<a name="_page114_x28.35_y391.36"></a>Ruta para el listado de elementos desde el layout

Agregamos la nueva ruta a los menús:

src\components\MenuHeader.vue src\components\MenuSidebar.vue

data() {![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.206.png)

return {

options: [

{

label: () =>

h(

RouterLink,

{

to: {

name: "list-element",

},

},

{ default: () => "Elements" } ),

key: "list type",

},

],

};![ref20]

},

En el ejemplo anterior, solamente agregamos la ruta a elementos y no por cada registro, como se hizo para el resto de las rutas, por lo tanto, podemos definir fácilmente la ruta a agregar desde la declaración de la propiedad.

<a name="_page115_x28.35_y101.61"></a>Detalle del elemento

Vamos a crear la página de detalle para los elementos, para esto, realizamos una petición a CRUDCRUD que devuelve el detalle del elemento dado el ID del mismo:

http://127.0.0.1:8000/api/element/1/

<template>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.207.png)

<div v-if="element != Object" class="max-w-lg">

<h1 class="text-4xl">

{{ element.title }}

</h1>

<p class="text-sm mt-5">

{{ element.price }}$

<span class="float-right"

\>{{ element.category\_id }} / {{ element.type\_id }}</span >

</p>

<hr class="my-4">

<p>{{ element.description }}</p>

</div>

</template>

<script>

export default {

data() {

return {

element: Object,

};

},

mounted() {

this.$axios

.get(

this.$root.urlCRUDElement +'/'+

this.$route.params.id

)

.then((res) => {

this.element = res.data;

});

},

};

</script>

**Explicación del código anterior**

El componente anterior es similar al que usamos para los listados, salvo que en esta oportunidad los datos devueltos por la Api es únicamente un objeto y no un listado de objetos, por lo tanto, en el template se muestra el detalle de un elemento con un CSS mínimo de Tailwind.

<a name="_page116_x28.35_y101.08"></a>Ruta de detalle del elemento

Creamos la ruta para el detalle por el slug:

const routes = [![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.208.png)

{

path: '/list', component: BasePage, children: [

// \*\*\*

],

}

**{**

**path: '/detail',**

**component: BasePage,**

**children: [**

**{**

**name: 'detail-element',**

**path: 'element/:id,**

**component: DetailElement }**

**]**

**}**

]

Y se modifica el componente de listado de elementos para agregar la opción de detalle del elemento: src\pages\ListElement.vue

//\*\*\*![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.209.png)

<n-button type="primary">

<router-link

:to="{ name: 'detail-element', params: { id: e.\_id } }" >Show</router-link

\>

</n-button>

//\*\*\*

<a name="_page116_x28.35_y651.50"></a>Extra: Introducción a sobrescribir el tema de NaiveUI

Seguramente si estás trabajando con NaiveUI te va a interesar sobrescribir el estilo, para ello, lo podemos hacer fácilmente importando un provider, definiendo en un objeto el estilo con las variables y valores a modificar y agrupando a tus componentes desde el componente padre con el estilo modificado:

/src/App.vue

<template>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.210.png)

**<n-config-provider :theme-overrides="themeOverrides">**

<n-layout class="mb-5">

<n-layout-header>

AppVue

<MenuHeader />

</n-layout-header>

</n-layout>

<n-space justify="center">

<n-layout has-sider>

<n-layout-sider><MenuSidebar /> </n-layout-sider>

<n-layout>

<n-layout-header> Title </n-layout-header> <n-layout-content class="p-2">

<router-view></router-view>

</n-layout-content>

<!-- <n-layout-footer>Footer</n-layout-footer> --> </n-layout>

</n-layout>

</n-space>

**</n-config-provider>**

</template>

<script>

**import { NConfigProvider } from "naive-ui";**

import MenuSidebar from "@/components/MenuSidebar"; import MenuHeader from "@/components/MenuHeader";

export default { components: { MenuSidebar, MenuHeader,

**NConfigProvider**, },

data() {

return { **themeOverrides: {**

**common: {**

**},**

**Table: {**

**tdColor:"#AAA"**

**}, },![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.060.png)**

**};**

},

}; </script>

Para conocer exactamente qué variables se pueden emplear; puedes revisar la página desde el botón de "Personalizar" y seleccionar un componente por vez y revisar cada una de las variables que puedes referenciar:

![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.211.jpeg)

Figura 4-8: Personalizar estilo NaiveUI

También recuerda que puedes sobrescribir el estilo con CSS de la manera tradicional, definiendo la clase y las reglas CSS; por ejemplo:

src\css\main.css

.n-button--primary-type{![ref6]

@apply bg-green-500 text-green-900 }

<a name="_page119_x28.35_y72.78"></a>Fase 2: CRUDs y formularios

En este apartado, vamos a completar el proceso CRUD con los formularios para crear y editar registros, además de habilitar la eliminación de los mismos como una acción más en la tabla de listado.

<a name="_page119_x28.35_y147.03"></a>Demo: Crear una categoría con validaciones en el servidor

Cómo comentamos en el capítulo anterior, al emplear el API de CRUDCRUD nos vemos un poco limitados en los desarrollos que podemos hacer, cómo las validaciones, por lo tanto, el siguiente apartado, lo puedes tomar como referencia cuando implementes tu aplicación en Vue consumiendo una API REST real.

Vamos a configurar un nuevo componente para que podamos crear categorías y mostrar los errores del servidor provistos por las validaciones.

Vamos a comenzar creando los **v-model** para las categorías y las propiedades para manejar el mensaje de los errores respectivamente:

form: {![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.209.png)

title: "",

url\_clean: "", },

errors: {

title: "", url\_clean: "",

},

Para el proceso de crear la categoría (**then()**) y capturar errores (**catch()**) de formularios que es la respuesta de tipo 400 (el tipo de respuesta devuelta cuando existen errores):

submit() {![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.212.png)

this.cleanForm();

this.$axios

.post("http://127.0.0.1:8000/api/category/", this.form) .then((res) => {

console.log(res.data);

})

.catch((error) => {

if (error.response.data.title)

this.errors.title = error.response.data.title[0];

if (error.response.data.url\_clean)

this.errors.url\_clean = error.response.data.url\_clean[0]; });

},

Los errores pueden estar presentes o no, depende de lo enviado por el usuario, y es por eso los condicionales que verifican si hay errores o no, si hay errores, entonces solamente mostramos el primero para el campo y lo establecemos en la propiedad en cuestión.

Limpiar los errores cada vez que hacemos un submit; esto es importante para evitar mostrar el estado anterior del formulario:

cleanForm() {![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.213.png)

this.errors.title = ""; this.errors.url\_clean = "";

},

Y en el template para el formulario: src\pages\save\SaveCategory.vue

<template>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.214.png)

<form @submit.prevent="submit">

<n-form-item

label="Title"

:feedback="errors.title"

:validaiton-status="errors.title == '' ? 'success' : 'error'"

\>

<n-input

:status="errors.title == '' ? 'success' : 'error'" placeholder="Title"

v-model:value="form.title"

type="text"

/>

</n-form-item>

<n-form-item

label="Slug"

:feedback="errors.url\_clean"

:validaiton-status="errors.url\_clean == '' ? 'success' : 'error'"

\>

<n-input

:status="errors.url\_clean == '' ? 'success' : 'error'" placeholder="Slug"

v-model:value="form.url\_clean"

type="text"

/>

</n-form-item>

<n-button class="mt-2" type="primary" attr-type="submit">Send</n-button> </form>

</template>

Lo único que hacemos de diferente es, colocar los **v-model** correspondientes y definir el componente de **n-form-item** que permite define un mensaje que podemos usar cuando ocurren los errores; para esto, verificamos las propiedades de errores para cada campo:

<n-form-item![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.145.png)

label="Title"

:feedback="errors.title"

:validaiton-status="errors.title == '' ? 'success' : 'error'"

\>

Para eso preguntamos por la condición del mensaje de los errores para cada campo.

El componente de **n-form-item** define un par de **props** que permiten:

- **feedback**, indicar el mensaje, en este caso, el del error si el mismo está presente.
- **validaiton-status**, indica el estado mediante un string, que puede ser, warning, success o error.

Y su ruta:

\*\*\* {![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.215.png)

path: '/save',

component: BasePage,

children: [

{

name: 'save-category',

path: 'category/',

component: SaveCategory }

]

},

Con esto, tendremos un componente como el siguiente:

![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.216.png)

Figura 4-9: Error de validación

Lo importante de notar del desarrollo anterior es que si exiten errores de validaciones con los datos del formulario, entonces la petición es capturada mediante el **catch()** en el cual se verifica el error y se establece(n) en la(s) propiedad(es) correspondiente(s).

<a name="_page122_x28.35_y426.60"></a>Editar una categoría

Para editar una categoría, se va a usar el mismo componente y ruta usado para la fase de crear; se va a indicar un parámetro opcional para saber si estamos en crear o editar; lógicamente, a la opción de editar es la que se le pasa el id:

\*\*\* {![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.217.png)

name: 'save-category', path: 'category/**:id?**', component: SaveCategory

} \*\*\*

Con esto, lo único qué hay que hacer es verificar si se recibe el id e inicializar el formulario y al hacer el submit, verificar si estamos creando o editando un registro:

<template>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.218.png)

\*\*\* </template>

<script>![ref26]

export default {

data() {

\*\*\*

},

**async mounted() {**

**if (this.$route.params.id) {**

**await this.getCategory(); // init this.initCategory();**

**}**

**},**

methods: {

**async getCategory() {**

**this.category = await this.$axios.get( this.$root.urlCRUDCategory +'/'+ this.$route.params.id**

**);**

**this.category = this.category.data;**

**},**

**initCategory() {**

**this.form.title = this.category.title; this.form.url\_clean = this.category.url\_clean;**

**},**

submit() {

this.cleanForm();

if (this.category == "")

**return** this.$axios

.post(this.$root.urlCRUDCategory, this.form)

.then((res) => {

console.log(res.data);

})

.catch((error) => {

if (error.response.data.title)

this.errors.title = error.response.data.title[0];

if (error.response.data.url\_clean)

this.errors.url\_clean = error.response.data.url\_clean[0];

});

**this.$axios .put(this.$root.urlCRUDCategory +'/'+**

**this.$route.params.id, this.form)![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.220.png)**

**.then((res) => {**

**console.log(res.data);**

**})**

**.catch((error) => {**

**if (error.response.data.title)**

**this.errors.title = error.response.data.title[0];**

**if (error.response.data.url\_clean)**

**this.errors.url\_clean = error.response.data.url\_clean[0];**

**});**

},

cleanForm() {

this.errors.title = "";

this.errors.url\_clean = ""; },

},

};

</script>

**Explicación del código anterior**

En la función de **mounted()** se verifica si existe un id, si existe, entonces se está en la fase de editar y hacen dos operaciones:

1. Buscar la categoría mediante la función de **getCategory()**.
1. Inicializar el formulario con la categoría obtenida en el paso 1 con la función de **initCategory()**.

Es importante que notes que se usa el **async** y **await** para buscar la categoría en vez de las funciones de promesa, para colocar un proceso bloqueante en la función de **mounted()** e inicializar el formulario solamente cuando se tiene la categoría.

Una vez se tiene la categoría y es inicializado el formulario, el siguiente paso es verificar en el **submit()** si se va a crear un registro o editar; para esto puedes usar el parámetro de la ruta o la categoría, en el código anterior, se usa la categoría, que sí está definida, significa que, se está en la fase de editar.

Por lo demás, es exactamente el mismo código presentado anteriormente.

<a name="_page124_x28.35_y542.51"></a>Enlaces para el CRUD

Ahora, se crean las opciones para crear y editar en el listado: src\pages\ListCategory.vue

\*\*\*![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.221.png)

**<n-button type="primary">**

**<router-link :to="{ name: 'save-category' }">Create</router-link> </n-button>**

<n-table :bordered="true" :single-line="false" class="my-table">

\*\*\*

**<router-link :to="{ name: 'save-category', params: { id: c.id } }"![ref27]**

**>Edit</router-link**

**>**

</td>

</tr>

</tbody>

\*\*\*

<a name="_page125_x28.35_y146.84"></a>Componente de formulario para el tipo

El componente para los tipos es exactamente el mismo usado para las categorías, pero referenciando los tipos en su lugar.

El componente para crear y editar luce: src\pages\save\SaveType.vue

<template>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.223.png)

<form @submit.prevent="submit">

<n-form-item

label="Title"

:feedback="errors.title"

:validaiton-status="errors.title == '' ? 'success' : 'error'"

\>

<n-input

:status="errors.title == '' ? 'success' : 'error'" placeholder="Title"

v-model:value="form.title"

type="text"

/>

</n-form-item>

<n-form-item

label="Slug"

:feedback="errors.url\_clean"

:validaiton-status="errors.url\_clean == '' ? 'success' : 'error'"

\>

<n-input

:status="errors.url\_clean == '' ? 'success' : 'error'" placeholder="Slug"

v-model:value="form.url\_clean"

type="text"

/>

</n-form-item>

<n-button class="mt-2" type="primary" attr-type="submit">Send</n-button> </form>

</template>

<script>![ref26]

export default {

data() {

return {

type: "",

form: {

title: "",

url\_clean: "", },

errors: {

title: "",

url\_clean: "", },

};

},

async mounted() {

if (this.$route.params.id) {

await this.getType();

// init

this.initType();

}

},

methods: {

async getType() {

this.type = await this.$axios.get( this.$root.urlCRUDType +'/'+ this.$route.params.id

);

this.type = this.type.data;

},

initType() {

this.form.title = this.type.title; this.form.url\_clean = this.type.url\_clean;

},

submit() {

this.cleanForm();

if (this.type == "")

return this.$axios

.post(this.$root.urlCRUDType, this.form) .then((res) => {

console.log(res.data);

})

.catch((error) => {![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.224.png)

if (error.response.data.title)

this.errors.title = error.response.data.title[0];

if (error.response.data.url\_clean)

this.errors.url\_clean = error.response.data.url\_clean[0];

});

this.$axios

.put(this.$root.urlCRUDType +'/'+

this.$route.params.id, this.form)

.then((res) => {

console.log(res.data);

})

.catch((error) => {

if (error.response.data.title)

this.errors.title = error.response.data.title[0];

if (error.response.data.url\_clean)

this.errors.url\_clean = error.response.data.url\_clean[0];

});

},

cleanForm() {

this.errors.title = "";

this.errors.url\_clean = ""; },

},

};

</script>

<a name="_page127_x28.35_y443.08"></a>Enlaces para el CRUD

De igual manera, se definen las rutas en el componente de listado: src\pages\ListType.vue

**<n-button type="primary">![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.225.png)**

**<router-link :to="{ name: 'save-type' }">Types</router-link>**

**</n-button>**

<n-table :bordered="true" :single-line="false" class="my-table">

\*\*\*

<td>

<n-button type="primary">

<router-link

:to="{ name: 'list-element', params: { type: 't', id: t.\_id } }" >Elements</router-link

\>

</n-button>

**<router-link :to="{ name: 'save-type', params: { id: t.\_id } }"![ref22]**

**>Types</router-link**

**>**

</td>

</tr>

</tbody>

\*\*\*

En cuando al momento de consumir el formulario desde el controlador, luce exactamente igual que el de tipos. Y la ruta:

\*\*\*![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.226.png)

{

path: '/save',

component: BasePage, children: [

{

name: 'save-type',

path: 'type/:id?',

component: SaveType }

]

},

\*\*\*

<a name="_page128_x28.35_y397.31"></a>Componente de formulario para el elemento

Este componente sigue el mismo lineamiento que el de categorías y tipos, pero con campos opcionales, ya que, los elementos cuentan aparte del título y el slug, con una descripción, precio y campos de selección para las categorías y tipos:

src\pages\save\SaveElement.vue

<template>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.227.png)

<form @submit.prevent="submit">

<n-form-item

label="Title"

:feedback="errors.title"

:validaiton-status="errors.title == '' ? 'success' : 'error'" >

<n-input

:status="errors.title == '' ? 'success' : 'error'" placeholder="Title"

v-model:value="form.title"

type="text"

/>

</n-form-item>

<n-form-item![ref26]

label="Slug"

:feedback="errors.url\_clean"

:validaiton-status="errors.url\_clean == '' ? 'success' : 'error'" >

<n-input

:status="errors.url\_clean == '' ? 'success' : 'error'" placeholder="Slug"

v-model:value="form.url\_clean"

type="text"

/>

</n-form-item>

<n-form-item

label="Description"

:feedback="errors.description"

:validaiton-status="errors.description == '' ? 'success' : 'error'" >

<n-input

:status="errors.description == '' ? 'success' : 'error'" placeholder="Description"

v-model:value="form.description"

type="textarea"

/>

</n-form-item>

<n-form-item

label="Price"

:feedback="errors.price"

:validaiton-status="errors.price == '' ? 'success' : 'error'"

\>

<n-input-number

:status="errors.price == '' ? 'success' : 'error'" placeholder="Price"

v-model:value="form.price"

type="textarea"

/>

</n-form-item>

<n-form-item

label="Category"

:feedback="errors.category\_id"

:validaiton-status="errors.category\_id == '' ? 'success' : 'error'" >

<n-select

:status="errors.category\_id == '' ? 'success' : 'error'" v-model:value="form.category\_id"

:options="categories\_options"

type="textarea"

/>

</n-form-item>

<n-form-item![ref26]

label="Type"

:feedback="errors.type\_id"

:validaiton-status="errors.type\_id == '' ? 'success' : 'error'" >

<n-select

:status="errors.type\_id == '' ? 'success' : 'error'" v-model:value="form.type\_id"

:options="tipes\_options"

type="textarea"

/>

</n-form-item>

<n-button class="mt-2" type="primary" attr-type="submit">Send</n-button> </form>

</template>

<script>

export default {

data() {

return {

categories\_options: [], tipes\_options: [], element: "",

form: {

title: "", url\_clean: "", description: "", category\_id: "", type\_id: "",

price: 0,

},

errors: {

title: "", url\_clean: "", description: "", category\_id: "", type\_id: "",

price: "",

},

};

},

async mounted() {

if (this.$route.params.id) {

await this.getElement(); // init

this.initElement(); }![ref26]

this.categories() this.tipes()

},

methods: {

async getElement() {

this.element = await this.$axios.get( this.$root.urlCRUDElement + this.$route.params.id

);

this.element = this.element.data;

},

initElement() {

this.form.title = this.element.title; this.form.url\_clean = this.element.url\_clean; this.form.description = this.element.description; this.form.price = parseFloat(this.element.price); this.form.category\_id = this.element.category\_id; this.form.type\_id = this.element.type\_id;

},

categories() {

this.$axios

.get(this.$root.urlCRUDCategory)

.then((res) => {

this.categories\_options = res.data.map(c => {

return {

label: c.title,

value: c.id

}

});

});

},

tipes() {

this.$axios

.get(this.$root.urlCRUDType)

.then((res) => {

this.tipes\_options = res.data.map(c => {

return {

label: c.title,

value: c.id

}

});

});

133
},

submit() {![ref26]

this.cleanForm();

if (this.element == "")

return this.$axios

.post(this.$root.urlCRUDElement, this.form)

.then((res) => {

console.log(res.data);

})

.catch((error) => {

console.log(error.response.data)

if (error.response.data.title)

this.errors.title = error.response.data.title[0];

if (error.response.data.url\_clean)

this.errors.url\_clean = error.response.data.url\_clean[0];

if (error.response.data.description)

this.errors.description = error.response.data.description[0]; if (error.response.data.price)

this.errors.price = error.response.data.price[0];

if (error.response.data.category\_id)

this.errors.category\_id = error.response.data.category\_id[0]; if (error.response.data.type\_id)

this.errors.type\_id = error.response.data.type\_id[0];

});

this.$axios

.put(this.$root.urlCRUDElement,

this.form

)

.then((res) => {

console.log(res.data);

})

.catch((error) => {

if (error.response.data.title)

this.errors.title = error.response.data.title[0];

if (error.response.data.url\_clean)

this.errors.url\_clean = error.response.data.url\_clean[0];

if (error.response.data.description)

this.errors.description = error.response.data.description[0]; if (error.response.data.price)

this.errors.price = error.response.data.price[0];

if (error.response.data.category\_id)

this.errors.category\_id = error.response.data.category\_id[0]; if (error.response.data.type\_id)

this.errors.type\_id = error.response.data.type\_id[0];

});

cleanForm() {

this.errors.title = "";

this.errors.url\_clean = ""; },

},

};

</script>

**Explicación del código anterior**

La estructura del componente para el guardado de los elementos es el mismo que el de categorías y tipos, pero con campos adicionales:

- Un campo de tipo TEXTAREA para la descripción; es el mismo que el que usa para el título, pero indicando en el tipo **type="textarea"**.
- Un campo de tipo numérico para el precio, en este caso, es otro componente llamado **n-input-number**.
- Dos campos de tipo selección mediante el componente de **n-select** que se usan para las categorías y tipos.

El componente de **n-select** para los campos de selección, recibe un **prop** llamado **options**, el cual recibe el listado de opciones con el siguiente formato:

options: [![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.228.png)

{

label: "Label 1", value: '1',

},

\*\*\* {

label: "Label 2", value: '2',

},

]

Dicho formato es diferente al que existe para las categorías y por lo tanto se hace el mapeo mediante la función **map()** para construir un nuevo listado con el formato correspondiente; este mapeo se hace en la función de promesa del axios que obtiene todos los registros de categorías y tipos para construir su respectivo campo de selección con el componente de NaiveUI:

this.$axios![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.229.png)

.get(this.$root.urlCRUDType)

.then((res) => {

this.tipes\_options = res.data**.map(c => {**

**return {**

**label: c.title,**

**value: c.id**

**}**

**});**

135
});![ref8]

Por lo demás, al tener atributos adicionales al resto de los componentes de guardado, hay más validaciones para los errores, definiciones en el template y manejo de los mismos.

<a name="_page134_x28.35_y86.80"></a>Definir Ruta

Se define la ruta de los elementos para crear y editar: \*\*\*![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.230.png)

{

path: '/save',

component: BasePage, children: [

{

name: 'save-element',

path: 'element/:id?',

component: SaveElement }

]

}

\*\*\*

<a name="_page134_x28.35_y346.95"></a>Enlaces para el CRUD

Y se definen las rutas de crear y editar en el listado: src\pages\ListElement.vue

\*\*\*![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.231.png)

**<n-button type="primary">**

**<router-link :to="{ name: 'save-element' }">Crear</router-link> </n-button>**

<n-table :bordered="true" :single-line="false" class="my-table">

\*\*\*

**<router-link :to="{ name: 'save-element', params: { id: e.\_id } }"**

**>Edit</router-link**

**>**

</td>

</tr>

</tbody>

\*\*\*

Y tendremos:

136

![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.232.jpeg)

Figura 4-9: Formulario para crear elementos

<a name="_page135_x28.35_y609.91"></a>CKEditor: Editor para el contenido enriquecido

Una necesidad común es la de querer incluir contenido enriquecido en una aplicación, entiéndase incorporar negritas, listados, tablas, títulos, etc; en el desarrollo web, se usan unos plugins llamados como What you see is what you get (en español, 'lo que ves es lo que obtienes') de sus siglas WYSIWYG los cuales permiten incorporar contenido HTML para tener el estilo deseado mediante un editor tipo “editor de texto” que podemos emplear en un computador como Libre Office, Word, etc.

137

CKEditor es un plugin WYSIWYG más; que al igual que el resto, etc o tenido enriquecido es HTML.

CKEditor cuenta con mucho tiempo en el mercado, lanzando revisiones periódicamente y extensible mediante plugins o paquetes para un montón de tecnologías; es un plugin que tiene un alcance enorme para personalizar y por lo tanto, es el que vamos a usar en este libro.

El plugin también está disponible para vanilla JavaScript mediante la CDN y Node y su instalación sería algo como esto:

$ npm install @ckeditor/ckeditor5-build-classic![ref17]

Empleando el tipo de Classic, pero, tenemos otros tipos aparte del Classic:

- Classic editor
- Inline editor
- Balloon editor
- Balloon block editor
- Document editor

Cuyo paquete varía según el tipo que quieras usar; en el libro se usará el de **Classic editor**. <a name="_page136_x28.35_y304.99"></a>Integrar Vue con CKEditor

Para integrar CKEditor con Vue, es necesario instalar dos dependencias:

1. El plugin para Vue.
1. El plugin de CKEditor en cualquiera de sus tipos.

Así que, para instalar CKEditor en Vue en su versión clásica:

$ npm install --save @ckeditor/ckeditor5-vue @ckeditor/ckeditor5-build-classic![ref24]

Para Vue, lo configuramos como un plugin más, similar a lo realizado con NaiveUI; la importación de CKEditor y la función **use()** para habilitar el plugin:

src\main.js

// \*\*\*![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.233.png)

import CKEditor from '@ckeditor/ckeditor5-vue'; // \*\*\*

app.use(CKEditor)

// \*\*\*

Ya con esto, podemos usar el plugin; para la aplicación que estamos desarrollando, vamos a adaptar el TEXTAREA para la descripción del componente de guardar los elementos, para que trabaje con CKEditor:

src\pages\save\SaveElement.vue <!-- <n-input![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.234.png)

:status="errors.description == '' ? 'success' : 'error'" placeholder="Description"

v-model:value="form.description" type="textarea"![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.235.png)

/> -->

<ckeditor :editor="editor.editor" v-model="form.description"></ckeditor> \*\*\*

import CKEditor from "@ckeditor/ckeditor5-build-classic";

export default {

data() {

return {

editor: {

editor: CKEditor, },

\*\*\*

**Explicación del código anterior**

1. Lo primero que debemos hacer es importar el editor; cual importes depende de cual instalastes; en el libro es el de clásico.
1. Luego, se configura una propiedad indicando el editor; con esta propiedad se pueden configurar otros aspectos como el contenido inicial y opciones que puedes ver en el siguiente enlace: <https://ckeditor.com/docs/ckeditor5/latest/installation/getting-started/frameworks/vuejs-v3.html>
1. Ya con esto, se puede usar el componente de **ckeditor** indicando el v-model y editor.

Y tendrás:

![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.236.jpeg)

Figura 4-10: Ckeditor

<a name="_page138_x28.35_y378.91"></a>Habilitar el CSS para CKEditor

Por defecto, al estar usando Tailwind CSS, este, elimina el diseño que se emplea por defecto, como el tamaño de los títulos, espaciados y viñetas para los listados, etc; pero, podemos indicar un estilo específico para estos componentes:

src\css\main.css

.ck-editor\_\_editable\_inline {![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.237.png)

min-height: 400px;

}

.ck-editor\_\_main h1 {

font-size: 40px; }

.ck-editor\_\_main h2 {

font-size: 30px; }

.ck-editor\_\_main h3 {

font-size: 25px; }

.ck-editor\_\_main h4 {![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.238.png)

font-size: 20px; }

.ck-editor\_\_main ul {

list-style-type: circle; margin: 10px;

padding: 10px;

}

.ck-editor\_\_main ol {

list-style-type: decimal; margin: 10px;

padding: 10px;

}

<a name="_page139_x28.35_y250.52"></a>Opciones de menús navegables

En apartados anteriores, se crearon el menú para el header y el sidebar los cuales tienen la misma estructura; en ambos casos, se muestran un listado de todas las categorías y tipos que existen en la Rest Api, pero, al darles click, no redirecciona a ninguna parte, y esto se debe a que, no se ha especificado la ruta mediante los **RouterLink**:

src\components\MenuSidebar.vue src\components\MenuHeader.vue

types() {![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.239.png)

this.$axios

.get(this.$root.urlCRUDType) .then((res) => {

const optionType = [

{

label: () =>

h(

RouterLink,

{

to: {

name: "list-type", },

},

{ default: () => "List" } ),

key: "list type",

},

];

res.data.forEach((t) => {

**optionType.push({**

**label: () =>**

**h(![ref26]**

**RouterLink,**

**{**

**to: { name: 'list-element', params: { type: 't', id: t.\_id } }, },**

**{ default: () => t.title }**

**),**

**key: "t " + t.\_id,**

**});**

});

this.options.push({

label: "Types",

key: "2 parent",

children: optionType,

});

});

},

categories() {

this.$axios

.get(this.$root.urlCRUDCategory)

.then((res) => {

const optionCate = [

{

label: () =>

h(

RouterLink,

{

to: {

name: "list-category",

},

},

{ default: () => "List" }

),

key: "list cate",

},

];

res.data.forEach((c) => {

**optionCate.push({**

**label: () =>**

**h(**

**RouterLink,**

**{**

**to: { name: 'list-element', params: { type: 'c', id: c.id } }, },**

**{ default: () => c.title }**

**),**

**key: "c " + c.id,**

**});**

144

}); this.options.push({![ref27]

label: "categories",

key: "1 parent",

children: optionCate, });

});

},

<a name="_page141_x28.35_y146.84"></a>Redirecciones en rutas inexistentes

Para evitar este tipo de advertencias cuando se ingresa a la aplicación desde alguna ruta que no existe, por ejemplo, la raíz:

[Vue Router warn]: No match found for location with path "/"![ref18]

Se van a definir unas rutas de tipo redirección, que, en vez de definir un componente, definen una función indicando la ruta a redireccionar:

src\router.js

// \*\*\*![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.240.png)

const routes = [

{

path: "/",

redirect: () => {

return { name: 'list-element' }

}

},

{

path: '/list',

component: BasePage,

children: [

{

path: "",

redirect: () => {

return { name: 'list-element' } }

},

// \*\*\*

En el código anterior, se usan rutas con nombres, para redireccionar a la vista de listado de elementos al acceder a:

http://localhost:8080/list

Va a redireccionar a la ruta señalada anteriormente.

También, en la redirección, puedes indicar el path:

redirect: () => {![ref2]

return { path: 'tuUri' } // ej list/element }

<a name="_page142_x28.35_y101.88"></a>Cambios visuales

Vamos a realizar algunos cambios finales más para la aplicación; por ejemplo, remover la clase de **max-w-lg** para la clase de la tabla:

.my-table{ @apply table-fixed max-w-lg mt-2 }![ref14]

Y queda como:

.my-table{ @apply table-fixed mt-2 }![ref17]

En el componente principal, vamos a remover el componente de **space**: src\App.vue

<n-space justify="center">![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.241.png)

Por un DIV con la clase **container** centrado:

<div class="container m-auto">![ref28]

Con esto, todas las vistas van a tener el mismo tamaño sin importar el contenido que se incluya, ya que, como estaba actualmente, el contenedor de **layout** se redimensiona según el contenido.

<a name="_page142_x28.35_y453.56"></a>Múltiples router-views

Otro aspecto que se va a tomar en cuenta, es el componente de **layout header**, que, de momento, muestra como contenido un texto que dice “title”, la idea es que, lo podamos personalizar para cada ruta; para esto, vamos a usar múltiples **router-view**.

Para poder usar múltiples **router-view** con Vue Router, tenemos que indicarle un nombre, como vemos a continuación:

<router-view **name="title"**></router-view> \*\*\*![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.234.png)

<router-view></router-view>

Si necesitas más, simplemente crear otro elemento **router-view** y le asignas otro nombre con el atributo **name**.

Aunque, el “problema” que tenemos actualmente, es que, estamos usando rutas agrupadas para todas las rutas de la aplicación:

\*\*\*![ref21]

path: '/list',

component: **BasePage**, children: [// All routes ] \*\*\*

Es decir, todas las rutas son procesadas dentro de **BasePage.vue** y es este que está incluido en el componente principal **App.vue**.

Por lo tanto, donde debe estar definida los múltiples **router-view**, es en el componente de **BasePage.vue** que es el que se emplea directamente con los componentes enrulados; as que, para esto, vamos a intercambiar el contenido de **App.vue** y de **BasePage.vue**.

src\App.vue

<template>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.243.png)

<router-view></router-view> </template>

src\pages\BasePage.vue

<template>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.244.png)

<n-config-provider :theme-overrides="themeOverrides">

<n-layout class="mb-5">

<n-layout-header>

AppVue

<MenuHeader />

</n-layout-header>

</n-layout>

<div class="container m-auto">

<n-layout has-sider>

<n-layout-sider><MenuSidebar /> </n-layout-sider>

<n-layout>

<n-layout-header> **<router-view name="title"></router-view>** </n-layout-header> <n-layout-content class="p-2">

**<router-view></router-view>**

</n-layout-content>

<!-- <n-layout-footer>Footer</n-layout-footer> -->

</n-layout>

</n-layout>

</div>

</n-config-provider>

</template>

<script>

import { NConfigProvider } from "naive-ui";

import MenuSidebar from "@/components/MenuSidebar"; import MenuHeader from "@/components/MenuHeader";![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.245.png)

export default { components: { MenuSidebar, MenuHeader,

NConfigProvider, },

data() {

return { themeOverrides: {

common: {

},

Table: {

tdColor:"#AAA" },

},

};

},

};

</script>

<style>

.n-layout-header,

.n-layout-footer {

background: rgba(128, 128, 128, 0.2); padding: 24px;

}

.n-layout-sider {

background: rgba(128, 128, 128, 0.3); }

.n-layout-content {

background: rgba(128, 128, 128, 0.4); }

</style>

Y con esto, para poder usar múltiples **router-view**, los componentes mencionados quedan como: src\router.js

components: {![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.246.png)

default: ListElement,

title: { template: 'Elements list' } }

En vez de:

src\router.js

component: ListElement![ref1]

Claro está, que debes de indicar el cambio para todas las rutas que quieras que usen el nuevo cambio; también debes de notar, que, para simplificar el proceso, se usa un template definido directamente en el script, pero, puedes usar un componente aparte, tal cual se ha hecho con los listados y formularios.

Otro aspecto a considerar es que, si usas el esquema de indicar el template directamente en el script, vas a notar una excepción cómo está en la consola del navegador:

Component provided template option but runtime compilation is not supported in this build of Vue. Configure your bundler to alias "vue" to "vue/dist/vue.esm-bundler.js".![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.247.png)

El problema es justamente lo que indica la excepción, se debe usar otro paquete para referenciar la instancia de Vue si quieres usar templates en el script; así que, debes de cambiar en el **src\main.js**:

import { createApp } from 'vue'![ref28]

A:

import { createApp } from 'vue/dist/vue.esm-bundler.js' ![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.248.png)Con esto, ahora sí, podrás ver los títulos definidos en la aplicación. Código fuente del capítulo: <https://github.com/libredesarrollo/curso-libro-naiveui>

<a name="_page146_x28.35_y28.34"></a>**Capítulo 5: Pinia**

En los proyectos anteriores hemos visto la necesidad de emplear un mismo conjunto de datos en varios lugares de la aplicación, es decir, vimos que la aplicación que creamos antes, tipo CRUD, tiene una organización como el siguiente árbol:

![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.249.jpeg)

Figura 5-1: Árbol de nodos que muestre los componentes y los hijos de una aplicación

Manejar un mismo pull de datos, o estados como lo hicimos en las aplicaciones anteriores mediante las publicaciones o elementos, vimos lo complicado que resulta poder sincronizar estos listados a lo largo de la aplicación, es decir, en todos estos nodos se emplea el mismo estado, el de elementos o publicaciones; el nodo principal es el de listado, pero, sus nodos hijos, nietos y demás descendencia son los que se encargan de manipular esta data, el problema es que, al momento de editar esta data muchas veces va a ser necesario comunicar a sus nodos padres, abuelos, hijos… los cambios realizados para que actualicen el estado que manejan localmente en cada nodo; esto, puede volver un verdadero reto si el árbol va creciendo al igual que la complejidad de los mismos.

Como hemos visto antes, el uso de los **props** y los eventos son buenos mecanismos para comunicarse entre componentes, pero hay que usarlos con cautela ya que no queremos que la mayor complejidad de los nodos (componentes hijos) sea definición de props, eventos y actualización de estados.

Otra posible solución es compartir la data desde el nodo principal a sus dependencias y realizar las actualizaciones directamente en este nodo principal.

En estos casos es útil emplear un gestor de estados como Pinia que permite centralizar o crear un singleton (instancia única) el estado de la aplicación para que luego pueda ser consumido y modificado fácilmente desde cualquier nodo de la aplicación; además de que al emplear un gestor de estado cómo Pinia podemos conservar la modularización de la aplicación, aspecto que se puede perder rápidamente si empleamos los esquemas anteriores.

Pinia es una biblioteca de gestión de estado para aplicaciones Vue 3 y es una de los plugins de gestión de estado mas populares junto con Vuex aunque Pinia a conseguido ganar popularidad al ser más simple que Vuex, Pinia elimina gran parte de la complejidad de Vuex en varias áreas: ya no existen módulos como tal, sino diferentes stores, cómo veremos más adelante, tampoco existen mutaciones porque no son necesarias para llevar el control de los cambios de estado (con la API de reactividad de Vue 3 expuesta, podemos mutar state desde cualquier lugar). Todo esto lo explicaremos más adelante en el capítulo. Además de que Pinia es extremadamente ligera, con un tamaño de alrededor de 1.5kb.

Existen otros manejadores de estados como Vuex, pero Pinia brilla por su sencillez, lo liviano que es el plugin, documentación y su buena acogida que tiene por parte de la comunidad de Vue.![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.250.png)

Para comenzar a usar Pinia, primero debes instalar el plugin y agregarlo a tu configuración. Luego, puedes crear una store con Pinia usando el método **defineStore()**.

<a name="_page147_x28.35_y325.28"></a>Ejemplo mínimo

Veamos un ejemplo mínimo de cómo emplear Pinia cómo gestor de estado para un contador en una aplicación en Vue; es importante que te fijes en la declaración del store mediante la función de **defineStore()** e internamente los tres componentes fundamentales, el **state** para declarar el contador, el **actions** para modificar el **state** y **getters** para obtener el **state**:

export const useCounterStore = defineStore('counter', {![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.251.png)

state: () => ({ count: 2 }),

getters: {

doubleCount: (state) => state.count \* 2,

},

actions: {

increment() {

this.count++

},

},

})

Esta es una sintaxis posible para definir nuestro store, es esta variación del store no empleamos objetos si no, una función y retornamos cada uno de los componentes del store; esta sintaxis es similar a la API de Vue Composition:

export const useCounterStore = defineStore('counter', ()=>{![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.091.png)

//state

const count = ref({ count: 2 })

// getters

const doubleCount = computed(()=> count.value.count \* 2); // actions![ref22]

function increment(){

count.value.count++;

}

return {doubleCount, increment}

})

- Los **ref()s** se convierten en propiedades de estado.
- Los **computed()s** se convierten en **getters**.
- Las funciones se convierten en **actions**

En ambos casos, desde un componente en Vue solamente debemos de crear una instancia del **store** de Pinia y emplearlo cómo si fuera un objeto; puedes emplear la sintaxis que prefieras.

<a name="_page148_x28.35_y233.84"></a>Conceptos claves

Teniendo como referencia la aplicación anterior, vamos a explicar algunos conceptos claves que debes de tener presente al momento de usar el gestor de estado de Pinia que son los siguientes:

- Store
- State
- Getters
- Actions

<a name="_page148_x28.35_y366.30"></a>Store

El store en Pinia se refiere a la data, el store puede ser más de uno dependiendo de las necesidades del proyecto y es donde se centraliza la data, puedes verlo cómo un almacén centralizado en donde se maneja el estado de la aplicación que luego es compartido entre los componentes de la aplicación en Vue, específicamente:

- Definición de los datos a compartir, por ejemplo un listado de posts, películas, datos de usuarios, etc, a esta capa se le conoce cómo **state**.
- Compartir los datos, ya que, no se accede directamente a los datos a compartir (state) si no, se comparten mediante una función, específicamente mediante funciones tipo “get”, esta capa es la llamada **getters**.
- Finalmente, es necesaria otra capa para modificar el **state**, que al igual que en el caso para la obtención del state (**getters**) no se puede hacer directamente, es decir, no se puede modificar directamente el state, para ello se emplean los **actions**.

El store se define mediante la función de **defineStore()** que recibe como primer parámetro el nombre del store.



|Un store en Pinia se crea utilizando la función **defineStore()** y puede contener el **state**, **getters** y **actions**.|
| - |
||
|El store en Pinia se configura mediante la definición de su estado inicial, **actions** y **getters**. Puede haber|
|múltiples **stores** en una aplicación, lo que permite una gestión modular y escalable del estado.|

Vamos a explicar los componentes del store un poco más en detalle:

<a name="_page149_x28.35_y28.34"></a>State

El estado es la parte central de la aplicación, ya que el resto de las capas dependen del estado. El estado es una función que retorna el estado inicial.

<a name="_page149_x28.35_y79.95"></a>Getters

Los **getters** no son más que propiedades computadas, es decir, de solo lectura o get para obtener los **state**.

<a name="_page149_x28.35_y133.01"></a>Actions

Las acciones no son más que métodos de un componente, en la cual se implementa la lógica de negocios para manipular la data, es decir, hacer el set en el **state**.

Puedes obtener más información en la documentación oficial: <https://pinia.vuejs.org/introduction.html>

<a name="_page149_x28.35_y258.81"></a>Aplicación con Vue y Pinia

Vamos a crear otro proyecto con Vue CLI cómo hablamos anteriormente en el capítulo 1; luego, instalamos la dependencia de Pinia mediante:

$ npm install pinia![ref18]

Y configuramos desde la instancia principal de Vue al igual que hacemos con cualquier otra dependencia: /src/main.js

import './assets/main.css'![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.251.png)

import { createApp } from 'vue' import { createPinia } from 'pinia' import App from './App.vue'

const app = createApp(App) app.use(createPinia()) app.mount('#app')

El pool de datos, puede ser cualquier sistema, cómo una base de datos local, un archivo de texto, algún conjunto de datos creado como respuesta a una solicitud a una Rest API, etc, en este ejemplo, corresponde a un listado estático:

src/data/posts.json [![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.252.png)

{

"id": 1,![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.253.png)

"title": "Post 1",

"body": " Lorem, ipsum dolor sit amet consectetur adipisicing elit. Quam fugit reiciendis maiores vitae a tenetur voluptas labore optio excepturi, dolorum possimus, soluta nobis expedita quos repellendus. Incidunt modi impedit possimus. "

},

{

"id": 2,

"title": "Post 2",

"body": " Lorem, ipsum dolor sit amet consectetur adipisicing elit. Quam fugit reiciendis maiores vitae a tenetur voluptas labore optio excepturi, dolorum possimus, soluta nobis expedita quos repellendus. Incidunt modi impedit possimus."

},

{

"id": 3,

"title": "Post 3",

"body": " Lorem, ipsum dolor sit amet consectetur adipisicing elit. Quam fugit reiciendis maiores vitae a tenetur voluptas labore optio excepturi, dolorum possimus, soluta nobis expedita quos repellendus. Incidunt modi impedit possimus."

}

]

El siguiente paso consiste en crear el **store**, en el cual implementamos los diversos métodos para gestionar la data, en este ejemplo, para obtener los datos, al igual que la cantidad de posts, por lo tanto, el **getters** no solamente debe ser empleado para retornar el **state**, si no, podemos procesar el state cómo en este ejemplo devolver la longitud:

src/stores/PostsStore.js

import {defineStore} from 'pinia'; import posts from '../data/posts.json';![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.254.png)

export const usePostsStore = defineStore('PostsStore', {

state: () => (

{

// posts: posts,

posts,

}

),

getters: {

postCount: (state) => state.posts.length, postsArray: (state) => state.posts,

},

actions: {

addPost(post){

this.posts.push(post);

},

},![ref20]

})

En el **actions** podemos realizar toda clase de manipulación cómo edición, eliminar o en este caso, agregar un post.

Y desde un componente en Vue, nos conectamos al **store** para obtener la datos e iterarlos y mostrar la cantidad total de publicaciones; es decir, se emplean los **getters** definidos:

src\components\Post\ListComponent.vue

<template>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.255.png)

<Item v-for="post in usePostsStore.postsArray" :key="post.id" :title="post.title" :content="post.body" />

<p>Total posts: {{ usePostsStore.postCount }}</p>

</template>

<script>

import {usePostsStore} from '@/stores/PostsStore'; import Item from '@/components/Post/ItemComponent.vue';

export default {

data() {

return {

usePostsStore

}

},

mounted() {

this.usePostsStore = usePostsStore() },

components:{

Item

}

}

</script>

En este otro componente usamos el **actions** para agregar una publicación: src\components\Post\InsertComponent.vue

<template>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.256.png)

<div>

<input v-model="title" type="text" /> <br>

<textarea v-model="content"/>

<br>

<button @click="save()">Add</button> </div>

</template>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.257.png)

<script>

import { usePostsStore } from '@/stores/PostsStore' export default {

mounted() {

this.usePostsStore = usePostsStore()

},

data(){

return {

usePostsStore,

title:"",

content:""

};

},

methods: {

save() {

this.usePostsStore.addPost({

id: this.usePostsStore.postCount + 1, title:this.title,

body:this.content

});

}

},

}

</script>

En este otro ejemplo, podemos ver otro componente que no emplea el store directamente, si no, es suministrado desde el componente padre mediante el uso de los props, por lo tanto, podemos emplear el enfoque tradicional de Vue junto con un manejador de estado cómo Pinia:

src\components\Post\ItemComponent.vue

<template>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.258.png)

<h1>{{title}}</h1>

<p>{{content}}</p> </template>

<script>

export default { props:{ title:String,

content:String }

}

</script>

Finalmente, en el **App.vue** cargamos los componentes de listado y para guardar un post:

src\App.vue

<template>![](Aspose.Words.4d672c10-168e-4b37-bf5f-a8d1be6a5dfa.259.png)

<h1>Posts</h1>

<List />

<Insert /> </template>

<script>

import Insert from '@/components/Post/InsertComponent.vue'; import List from '@/components/Post/ListComponent.vue';

export default { components:{

List, Insert

}

}

</script>

Como puedes apreciar, es muy sencillo su uso, lo importante es conocer para qué emplear cada componente del **store** (**actions**, **getters** y **state**) para la entidad que queramos emplear. Finalmente, puedes declarar tantos stores cómo necesites, por ejemplo, para categorías, datos de usuario, etc.

Código fuente de la sección: <https://github.com/libredesarrollo/curso-libro-vuepinia>
158

