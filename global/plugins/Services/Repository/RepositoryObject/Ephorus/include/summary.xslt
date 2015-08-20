<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output method="text" encoding="UTF-8" />
	<xsl:param name="guids"></xsl:param>

	<xsl:template match="/document">
		<xsl:apply-templates />
	</xsl:template>

	<xsl:template match="open">
		<xsl:if test="contains($guids, @id)">
			<![CDATA[
				<span class="summary_found">
			]]>
		</xsl:if>
	</xsl:template>

	<xsl:template match="close">
		<xsl:if test="contains($guids, @id)">
			<![CDATA[
				</span>
			]]>
		</xsl:if>
	</xsl:template>

	<xsl:template match="added">
		<xsl:choose>
			<xsl:when test="not(contains($guids, @id))">
				<xsl:apply-templates />
			</xsl:when>
			<xsl:otherwise>
				<![CDATA[
					<span class="summary_added">
				]]>
			<xsl:apply-templates />
				<![CDATA[
					</span>
				]]>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template match="text()">
		<xsl:call-template name="replace">
			<xsl:with-param name="string" select="." />
		</xsl:call-template>
	</xsl:template>

	<xsl:template name="replace">
		<xsl:param name="string"/>
		<xsl:choose>
			<xsl:when test="contains($string,'&#10;')">
				<xsl:value-of select="substring-before($string,'&#10;')" />
					<![CDATA[ <br> ]]>
				<xsl:call-template name="replace">
					<xsl:with-param name="string" select="substring-after($string,'&#10;')"/>
				</xsl:call-template>
			</xsl:when>
			<xsl:otherwise>
				<xsl:value-of select="$string" />
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>
</xsl:stylesheet>