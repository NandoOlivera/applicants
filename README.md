# Desarrollo de la prueba

## Errores simples en /chart:

A la hora de inicializar el array **$data**, el nombre del array _‘hashtags’_ estaba escrito como _‘hashtag’_ y faltaba inicializar el array _‘user’_.
En la función **fillDates**, a la hora de calcular la fecha máxima que debe mostrar la gráfica, no se estaba tomando en cuenta los valores de **$dates['user']**.

## Errores en la DB:

El campo **twitter_created_at** de la tabla **twitter_tweets** era del tipo **VARCHAR(255)** y no había consistencia en el **formato de la fecha/hora** de los registros. Por ejemplo había registros con formato _‘Tue Jan 11 17:18:53 +0000 2011’_ y otros con formato _‘2011-10-03T18:08:21.000Z’_. 
Esto entre otros problemas, hacía que la consulta trajera registros que no cumplian con el rango de fecha esperado. Cuando por ejemplo se evaluaba `WHERE twitter_created_at > '2014-08-22'`, si el registro tenía fecha  _‘Tue Jan 11 17:18:53 +0000 2011’_, iba a cumplir con la condición cuando no debería.

Para solucionar esto, lo que hice fue crear una migración de la DB (_NormalizarFechaTweets_) utilizando la herramienta sugerida ( _Phinx_ ). Esta migración lo que hace es normalizar los registros de la columna **twitter_created_at**, dejandolos con formato _Y-m-d H:i:s_. Y una vez hecho esto, cambia el tipo de campo de **VARCHAR** a **DATETIME**. Es de esperar que el script que agrega los tweets a la DB (el cual no estaba en el repositorio), formatee la fecha/hora del tweet antes de ingresarlo a la DB.
Con el fin de que puedan evaluarlo más fácil, agregué a **boostrap&#46;sh** un comando para que se haga la migración ni bien se cree el box de Vagrant. [Creé la migración en vez de usar la que estaba para que el nombre fuera más descriptivo.] 

## Errores en las consultas SQL:

Al hacer `group by twitter_created_at`, se estaba agrupando por **fecha/hora** y no solo por **fecha** como era lo que se esperaba. Esto hacía que la consulta trajera más registros de los necesarios, ya que tweets realizados en un mismo dia pero en un diferente momento (_H:i:s_) no se agrupaban para obtener la cantidad de los mismos. Si bien después los valores eran correctos por la sumatoria de cantidades que se hace en el código, lo correcto es que se agruparan por fecha. Para solucionar esto utilicé la función **DATE** de **MySQL** de forma que se agrupen por fecha.


 >#### “hay una brecha donde no hay datos que es muy extraña” 
 Una vez hechos los cambios en la DB y código, se sigue viendo una brecha en la cual **si hay valores**, pero que en comparación con la magnitud de los máximos de la gráfica, son casi insignificantes. Estuve examinando la DB y creo que los datos que se muestran son correctos. Al menos son los relativos a la tabla **twitter_tracking**, que es lo que entendí que debía mostrar la aplicación.




## “Hacer que el resultado de la llamada a /chart sea reutilizable en otros dispositivos”

Lo que hice fue trasladar el _html_ de _**chart.twig**_ (que contiene divs , estilos y código específicos) a _**index.twig**_ y transformé _/chart_ en un _endpoint_ que devuelve un _json_ con los valores necesarios para crear la gráfica. De esta manera, otra versión de la aplicación puede usar estos valores customizando el formato/diseño de la gráfica . De hecho podrían usar otra librería de gráficas (por ejemplo en una versión nativa). 


## Tiempo de carga de la gráfica:

Para solucionar esto, lo que haría sería guardar el response del endpoint en un archivo estático. Por ejemplo, como la gráfica muestra las cantidades por día, podría crearse un _cron job_ que después de finalizado el día, ejecute un script que obtenga la data de los tweets del día anterior y la agregue al response (_json_ estático). Estas consultas no demorarian tanto ya que sólo obtendrían los registros del día anterior. De esta manera todos los request a _/chart_ lo único que harían es devolver el archivo estático sin necesidad de consultar la DB. Esto no lo implementé porque no está en el repositorio la funcionalidad que agrega los tweets y por ende el script del _cron job_ no agregaría nada, o sea no habría nuevos tweets para que vean el funcionamiento.

Algo que sí hice y que complementa la solución anterior, es agregar los headers **Expires** y **Etag** al response, poniendole como id (a _Etag_) y fecha/hora de expiración (a _Expires_) la fecha/hora en que finaliza el día. De esta manera, una vez que el cliente hace un request a _/chart_, por el resto del día el navegador **utiliza el response que tiene en caché**, en vez de volver a hacer un request (ya que el response no va a cambiar hasta que finalice el día y se agreguen los nuevos tweets).