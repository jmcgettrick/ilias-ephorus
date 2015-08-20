<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:str="http://exslt.org/strings" >
	<xsl:strip-space elements="*" />
	<xsl:output method="html" media-type="text/html" encoding="UTF-8" />
	<xsl:decimal-format name="european" decimal-separator=',' grouping-separator='.' />

	<xsl:param name="original"></xsl:param>
	<xsl:param name="found"></xsl:param>

	<xsl:template match="/document">
		<table border="0" width="100%" cellpadding="0" cellspacing="0" >
			<xsl:apply-templates />
		</table>
	</xsl:template>

	<xsl:template match="fragment">
		<tr>
			<td colspan="2" class="summary_original">
				<xsl:call-template name="replace-string">
					<xsl:with-param name="text" select="."/>
					<xsl:with-param name="from" select="'&#10;'"/>
					<xsl:with-param name="to" select="'&lt;br/&gt;'"/>
				</xsl:call-template>
			</td>
		</tr>
	</xsl:template>

	<xsl:template match="result">
		<tr>
			<td><h3><xsl:value-of select="$original" disable-output-escaping="yes" />:</h3></td>
			<td><h3><xsl:value-of select="$found" disable-output-escaping="yes" />:</h3></td>
		</tr>
		<tr>
			<td valign="top" class="result_original">
				<div>
					<xsl:apply-templates select="origineel | addedword"/>
				</div>
			</td>
			<td valign="top" class="result_found">
				<div>
					<xsl:apply-templates select="vergelijk | removedword"/>
				</div>
			</td>
		</tr>
	</xsl:template>

	<xsl:template match="origineel">
		<xsl:call-template name="replace-string">
			<xsl:with-param name="text" select="."/>
			<xsl:with-param name="from" select="'&#10;'"/>
			<xsl:with-param name="to" select="'&lt;br/&gt;'"/>
		</xsl:call-template>
	</xsl:template>

	<xsl:template match="vergelijk">
		<xsl:call-template name="replace-string">
			<xsl:with-param name="text" select="."/>
			<xsl:with-param name="from" select="'&#10;'"/>
			<xsl:with-param name="to" select="'&lt;br/&gt;'"/>
		</xsl:call-template>
	</xsl:template>

	<xsl:template match="addedword">
		<span class="summary_added">
			<xsl:call-template name="replace-string">
				<xsl:with-param name="text" select="."/>
				<xsl:with-param name="from" select="'&#10;'"/>
				<xsl:with-param name="to" select="'&lt;br/&gt;'"/>
			</xsl:call-template>
		</span>
	</xsl:template>

	<xsl:template match="removedword">
		<span class="summary_removed">
			<xsl:call-template name="replace-string">
				<xsl:with-param name="text" select="."/>
				<xsl:with-param name="from" select="'&#10;'"/>
				<xsl:with-param name="to" select="'&lt;br/&gt;'"/>
			</xsl:call-template>
		</span>
	</xsl:template>

	<xsl:template match="*">
	</xsl:template>

	<xsl:template name="br" >
		<br/>
	</xsl:template>

	<xsl:template name="replace-string" >
		<xsl:param name="text"/>
		<xsl:param name="from"/>
		<xsl:param name="to"/>

		<xsl:choose>
			<xsl:when test="contains($text, $from)">
				<xsl:variable name="before" select="substring-before($text, $from)"/>
				<xsl:variable name="after" select="substring-after($text, $from)"/>
				<xsl:variable name="prefix" select="concat($before, $to)"/>

				<xsl:value-of select="$before" disable-output-escaping="yes"/>
				<xsl:value-of select="$to" disable-output-escaping="yes"/>
				<xsl:call-template name="replace-string">
					<xsl:with-param name="text" select="$after"/>
					<xsl:with-param name="from" select="$from"/>
					<xsl:with-param name="to" select="$to"/>
				</xsl:call-template>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="$text" disable-output-escaping="yes" />
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
</xsl:stylesheet>