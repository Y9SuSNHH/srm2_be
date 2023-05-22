<?php

namespace App\Http\Domain\Reports\Services;

use App\Http\Domain\Reports\Repositories\G120\G120RepositoryInterface;
use App\Http\Enum\ProfileStatus;
use App\Http\Enum\LockDay;
use Carbon\Carbon;

class XmlGenerator
{
    public function generateXMLTags(array $tags)
    {
        $xml_tags = array_map(function($tag) {
            $element = new \DOMDocument('1.0','UTF-8');
            // Create outer tag
            $new_el = $element->createElement($tag['name']);
            $element->appendChild($new_el);

            if(str_contains($tag['name'],'text'))
            {
                $new_el->textContent = isset($tag['value']) ? $tag['value'] : '';
            }

            // If you have any optional attributes, they'll be generated here
            if(!empty($tag['attributes']))
            {
                foreach($tag['attributes'] as $attr)
                {
                    $tag_attr = $element->createAttribute($attr['name']);
                    $tag_attr->value = $attr['value'];
                    $new_el->appendChild($tag_attr);
                }
            }
            // Element inside the outer tag
            if(!empty($tag['children']))
            {
                $tag_children = array_map(function($child) use($element) {
                    $child_field = $element->createElement($child['name']);
                    if(str_contains($child['name'],'text'))
                    {
                        $child_field->textContent = isset($child['value']) ? $child['value'] : '';
                    }

                    if(!empty($child['attributes']))
                    {
                        foreach($child['attributes'] as $attr)
                        {
                            $tag_attr = $element->createAttribute($attr['name']);
                            $tag_attr->value = $attr['value'];
                            $child_field->appendChild($tag_attr);
                        }
                    }
                    if(isset($child['children']))
                    {
                        foreach($child['children'] as $grand_child)
                        {
                            $grand_child_field = $element->createElement($grand_child['name']);
                            $grand_child_field->textContent = $grand_child['value'] ?? '';
                            $child_field->appendChild($grand_child_field);
                        }
                    }
                    return $child_field;
                },$tag['children']);

                foreach($tag_children as $tag)
                {
                    $new_el->appendChild($tag);
                }
            }
            $raw_output = str_replace('<?xml version="1.0" encoding="UTF-8"?>', '', $element->saveXML());
            $out_put = trim($raw_output);

            return $out_put;
        },$tags);

        return implode(' \n',$xml_tags);
    }

    public function fodsFileHeader()
    {
        return '<?xml version="1.0" encoding="UTF-8"?>
                <office:document xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0" xmlns:ooo="http://openoffice.org/2004/office" xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:config="urn:oasis:names:tc:opendocument:xmlns:config:1.0" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0" xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0" xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0" xmlns:rpt="http://openoffice.org/2005/report" xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0" xmlns:dr3d="urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0" xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0" xmlns:chart="urn:oasis:names:tc:opendocument:xmlns:chart:1.0" xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0" xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0" xmlns:ooow="http://openoffice.org/2004/writer" xmlns:oooc="http://openoffice.org/2004/calc" xmlns:of="urn:oasis:names:tc:opendocument:xmlns:of:1.2" xmlns:xforms="http://www.w3.org/2002/xforms" xmlns:tableooo="http://openoffice.org/2009/table" xmlns:calcext="urn:org:documentfoundation:names:experimental:calc:xmlns:calcext:1.0" xmlns:drawooo="http://openoffice.org/2010/draw" xmlns:loext="urn:org:documentfoundation:names:experimental:office:xmlns:loext:1.0" xmlns:field="urn:openoffice:names:experimental:ooo-ms-interop:xmlns:field:1.0" xmlns:math="http://www.w3.org/1998/Math/MathML" xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0" xmlns:script="urn:oasis:names:tc:opendocument:xmlns:script:1.0" xmlns:formx="urn:openoffice:names:experimental:ooxml-odf-interop:xmlns:form:1.0" xmlns:dom="http://www.w3.org/2001/xml-events" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:grddl="http://www.w3.org/2003/g/data-view#" xmlns:css3t="http://www.w3.org/TR/css3-text/" xmlns:presentation="urn:oasis:names:tc:opendocument:xmlns:presentation:1.0" office:version="1.3" office:mimetype="application/vnd.oasis.opendocument.spreadsheet">
                    <office:meta>
                        <meta:creation-date>2022-12-15T11:22:11.243000000</meta:creation-date>
                        <dc:date>2022-12-15T11:35:10.172000000</dc:date>
                        <meta:editing-duration>PT6M11S</meta:editing-duration>
                        <meta:editing-cycles>8</meta:editing-cycles>
                        <meta:generator>LibreOffice/7.4.2.3$Windows_X86_64 LibreOffice_project/382eef1f22670f7f4118c8c2dd222ec7ad009daf</meta:generator>
                        <meta:document-statistic meta:table-count="1" meta:cell-count="59" meta:object-count="0"/>
                    </office:meta>';
    }
}
