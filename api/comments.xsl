<?xml version="1.0" encoding="utf-8" ?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
    <xsl:template match="posts">
        <xsl:for-each select="post">
            <xsl:value-of select="id" />
            <xsl:value-of select="name" />
            <xsl:value-of select="message" />
            <xsl:value-of select="parent_id" />
            <xsl:value-of select="time" />
        </xsl:for-each>
    </xsl:template>
</xsl:stylesheet>