<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
  version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:s="http://www.sitemaps.org/schemas/sitemap/0.9"
>
  <xsl:output method="html" encoding="UTF-8" indent="yes" />

  <xsl:template match="/">
    <html lang="es">
      <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>Mapa del sitio | Camino del Dharma</title>
        <style>
          :root {
            --brand-1: #8c2b3d;
            --brand-3: #d1aeab;
            --brand-4: #3e424b;
            --brand-1-deep: #7a2436;
          }

          * {
            box-sizing: border-box;
          }

          body {
            margin: 0;
            padding: 2.5rem 1.25rem 3rem;
            font-family: Inter, system-ui, -apple-system, "Segoe UI", sans-serif;
            font-size: 1rem;
            line-height: 1.6;
            color: var(--brand-4);
            background: var(--brand-3);
          }

          .wrap {
            max-width: 56rem;
            margin: 0 auto;
          }

          header {
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid rgb(62 66 75 / 15%);
          }

          h1 {
            margin: 0 0 0.5rem;
            font-family: "Fjalla One", "Arial Narrow", sans-serif;
            font-size: clamp(1.75rem, 4vw, 2.25rem);
            font-weight: 400;
            letter-spacing: 0.02em;
            color: var(--brand-1);
          }

          .intro {
            margin: 0;
            max-width: 42rem;
            color: var(--brand-4);
          }

          .meta {
            margin-top: 1rem;
            font-size: 0.9375rem;
            color: rgb(62 66 75 / 75%);
          }

          table {
            width: 100%;
            border-collapse: collapse;
            background: rgb(255 255 255 / 35%);
            border: 1px solid rgb(62 66 75 / 12%);
          }

          caption {
            padding: 0 0 0.75rem;
            text-align: left;
            font-weight: 600;
            color: var(--brand-4);
          }

          th,
          td {
            padding: 0.875rem 1rem;
            text-align: left;
            vertical-align: top;
            border-bottom: 1px solid rgb(62 66 75 / 10%);
          }

          th {
            font-size: 0.8125rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: var(--brand-1-deep);
            background: rgb(255 255 255 / 25%);
          }

          tr:last-child td {
            border-bottom: 0;
          }

          a {
            color: var(--brand-1-deep);
            text-decoration: underline;
            text-underline-offset: 0.15em;
            word-break: break-word;
          }

          a:hover,
          a:focus {
            color: var(--brand-1);
          }

          .path {
            display: block;
            margin-top: 0.25rem;
            font-size: 0.875rem;
            color: rgb(62 66 75 / 70%);
          }

          footer {
            margin-top: 2rem;
            font-size: 0.875rem;
            color: rgb(62 66 75 / 70%);
          }

          footer a {
            font-weight: 600;
          }
        </style>
      </head>
      <body>
        <div class="wrap">
          <header>
            <h1>Mapa del sitio</h1>
            <p class="intro">
              Listado de páginas públicas de Camino del Dharma.
            </p>
            <p class="meta">
              <xsl:value-of select="count(s:urlset/s:url)" /> URLs indexadas
            </p>
          </header>

          <table>
            <caption>Páginas</caption>
            <thead>
              <tr>
                <th scope="col">URL</th>
                <th scope="col">Última modificación</th>
              </tr>
            </thead>
            <tbody>
              <xsl:for-each select="s:urlset/s:url">
                <tr>
                  <td>
                    <a href="{s:loc}">
                      <xsl:value-of select="s:loc" />
                    </a>
                    <span class="path">
                      <xsl:value-of select="substring-after(s:loc, 'https://caminodeldharma.org')" />
                      <xsl:if test="substring-after(s:loc, 'https://caminodeldharma.org') = ''">/</xsl:if>
                    </span>
                  </td>
                  <td>
                    <xsl:value-of select="s:lastmod" />
                  </td>
                </tr>
              </xsl:for-each>
            </tbody>
          </table>

          <footer>
            <p>
              <a href="https://caminodeldharma.org">Volver al inicio</a>
            </p>
          </footer>
        </div>
      </body>
    </html>
  </xsl:template>
</xsl:stylesheet>
