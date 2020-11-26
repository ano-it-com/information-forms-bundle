<?php

namespace ANOITCOM\InformationFormsBundle\Services\Covid;

use ANOITCOM\Wiki\Entity\Objects\DataObject;
use ANOITCOM\Wiki\Entity\Objects\ObjectType;
use ANOITCOM\Wiki\Entity\Objects\ObjectTypeField;
use ANOITCOM\Wiki\Entity\Objects\Values\ObjectLinkValue;
use ANOITCOM\Wiki\Entity\Objects\Values\ObjectLiteralValue;
use ANOITCOM\Wiki\Entity\WikiPage\Page;
use ANOITCOM\Wiki\Entity\WikiPageBlocks\WikiPageBlocks\WikiPageObjectBlock;
use Doctrine\ORM\EntityManagerInterface;

class CovidObjectsService
{

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    protected $regionType;


    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->regionType    = isset($_ENV['REGION_TYPE']) ? $_ENV['REGION_TYPE'] : null;

    }


    public function createObjects($date = null)
    {
        if ($date === null) {
            $date = (new \DateTime())->format('d.m.Y');
        }

        foreach ($this->getObjectsTypes() as $covidObjectTypeId) {
            /** @var ObjectType $covidObjectType */
            $covidObjectType = $this->entityManager->getRepository(ObjectType::class)->find($covidObjectTypeId);

            if ( ! $covidObjectType) {
                continue;
            }

            foreach ($this->getRegions() as $regionName) {
                /** @var DataObject $regionObject */
                $regionObject = $this->entityManager->getRepository(DataObject::class)->findOneBy([ 'title' => $regionName, 'type' => $this->regionType ]);

                if ( ! $regionObject) {
                    continue;
                }

                $title = sprintf('%s (%s)', $covidObjectType->getTitle(), $date);

                $covidObject = new DataObject();
                $covidObject->setTitle($title);
                $covidObject->setType($covidObjectType);

                foreach ($covidObjectType->getFields() as $objectTypeField) {
                    if ($objectTypeField->getSubtype() && $objectTypeField->getSubtype()->getName() === 'date') {
                        $this->createLiteralValue($covidObject, $objectTypeField->getId(), $date);
                    }

                    if ( ! $objectTypeField->getRanges()->isEmpty()) {
                        foreach ($objectTypeField->getRanges() as $range) {
                            if ($range->getId() === $this->regionType) {
                                $this->createLinkValue($covidObject, $objectTypeField->getId(), $regionObject);
                            }
                        }
                    }
                }

                $wikiCovidObjectPage = new Page();
                $wikiCovidObjectPage->setTitle($title);
                $wikiCovidObjectPage->setFilesEnabled(false);
                $wikiCovidObjectPage->setHideTableOfContents(true);
                $wikiCovidObjectPage->setHideComments(true);

                $wikiCovidObjectPage->setParent($regionObject->getPage());

                $this->entityManager->persist($wikiCovidObjectPage);
                $this->entityManager->persist($covidObject);

                $covidObject->setPage($wikiCovidObjectPage);

                $wikiCovidObjectBlock = new WikiPageObjectBlock();
                $wikiCovidObjectBlock->setTitle($objectTypeField->getTitle());
                $wikiCovidObjectBlock->setAside(true);
                $wikiCovidObjectBlock->setSort(0);
                $wikiCovidObjectBlock->setAccessLevel(0);
                $wikiCovidObjectBlock->setMain(true);
                $wikiCovidObjectBlock->addObject($covidObject);
                $wikiCovidObjectBlock->setPage($wikiCovidObjectPage);
                $wikiCovidObjectPage->addBlock($wikiCovidObjectBlock);

                $this->entityManager->persist($wikiCovidObjectBlock);

                $this->entityManager->flush();
            }
        }

    }


    protected function getObjectsTypes()
    {
        return [
            $_ENV['COVID_REPORT_TYPE'],
            $_ENV['COVID_TEST_TYPE'],
        ];
    }


    public function getRegions()
    {
        return [
            'АДЫГЕЯ (01)',
            'АЛТАЙ РЕСП. (04)',
            'АЛТАЙСКИЙ КРАЙ (22)',
            'АМУРСКАЯ ОБЛ (28)',
            'АРХАНГЕЛЬСКАЯ ОБЛ (29)',
            'АСТРАХАНСКАЯ ОБЛ (30)',
            'БАШКОРТОСТАН (02)',
            'БЕЛГОРОДСКАЯ ОБЛ (31)',
            'БРЯНСКАЯ ОБЛ (32)',
            'БУРЯТИЯ (03)',
            'ВЛАДИМИРСКАЯ ОБЛ (33)',
            'ВОЛГОГРАДСКАЯ ОБЛ (34)',
            'ВОЛОГОДСКАЯ ОБЛ (35)',
            'ВОРОНЕЖСКАЯ ОБЛ (36)',
            'ДАГЕСТАН (05)',
            'ЕВРЕЙСКАЯ АО (79)',
            'ЗАБАЙКАЛЬСКИЙ КРАЙ (75)',
            'ИВАНОВСКАЯ ОБЛ (37)',
            'ИНГУШЕТИЯ (06)',
            'ИРКУТСКАЯ ОБЛ (38)',
            'КАБАРДИНО-БАЛКАРСКАЯ (07)',
            'КАЛИНИНГРАДСКАЯ ОБЛ (39)',
            'КАЛМЫКИЯ (08)',
            'КАЛУЖСКАЯ ОБЛ (40)',
            'КАМЧАТСКИЙ КРАЙ (41)',
            'КАРАЧАЕВО-ЧЕРКЕССИЯ (09)',
            'КАРЕЛИЯ (10)',
            'КЕМЕРОВСКАЯ ОБЛ (42)',
            'КИРОВСКАЯ ОБЛ (43)',
            'КОМИ (11)',
            'КОСТРОМСКАЯ ОБЛ (44)',
            'КРАСНОДАРСКИЙ КРАЙ (23)',
            'КРАСНОЯРСКИЙ КРАЙ (24)',
            'КРЫМ (91)',
            'КУРГАНСКАЯ ОБЛ (45)',
            'КУРСКАЯ ОБЛ (46)',
            'ЛЕНИНГРАДСКАЯ ОБЛ (47)',
            'ЛИПЕЦКАЯ ОБЛ (48)',
            'МАГАДАНСКАЯ ОБЛ (49)',
            'МАРИЙ ЭЛ (12)',
            'МОРДОВИЯ (13)',
            'МУРМАНСКАЯ ОБЛ (51)',
            'НЕНЕЦКИЙ АО (83)',
            'НИЖЕГОРОДСКАЯ ОБЛ (52)',
            'НОВГОРОДСКАЯ ОБЛ (53)',
            'НОВОСИБИРСКАЯ ОБЛ (54)',
            'ОМСКАЯ ОБЛ (55)',
            'ОРЕНБУРГСКАЯ ОБЛ (56)',
            'ОРЛОВСКАЯ ОБЛ (57)',
            'ПЕНЗЕНСКАЯ ОБЛ (58)',
            'ПЕРМСКИЙ КРАЙ (59)',
            'ПРИМОРСКИЙ КРАЙ (25)',
            'ПСКОВСКАЯ ОБЛ (60)',
            'РОСТОВСКАЯ ОБЛ (61)',
            'РЯЗАНСКАЯ ОБЛ (62)',
            'САМАРСКАЯ ОБЛ (63)',
            'САНКТ-ПЕТЕРБУРГ (78)',
            'САРАТОВСКАЯ ОБЛ (64)',
            'САХА (14)',
            'САХАЛИНСКАЯ ОБЛ (65)',
            'СВЕРДЛОВСКАЯ ОБЛ (66)',
            'СЕВАСТОПОЛЬ (92)',
            'СЕВЕРНАЯ ОСЕТИЯ (15)',
            'СМОЛЕНСКАЯ ОБЛ (67)',
            'СТАВРОПОЛЬСКИЙ КРАЙ (26)',
            'ТАМБОВСКАЯ ОБЛ (68)',
            'ТАТАРСТАН (16)',
            'ТВЕРСКАЯ ОБЛ (69)',
            'ТОМСКАЯ ОБЛ (70)',
            'ТУЛЬСКАЯ ОБЛ (71)',
            'ТЫВА (17)',
            'ТЮМЕНСКАЯ ОБЛ (72)',
            'УДМУРТИЯ (18)',
            'УЛЬЯНОВСКАЯ ОБЛ (73)',
            'ХАБАРОВСКИЙ КРАЙ (27)',
            'ХАКАСИЯ (19)',
            'ХАНТЫ-МАНСИЙСКИЙ АО (86)',
            'ЧЕЛЯБИНСКАЯ ОБЛ (74)',
            'ЧЕЧЕНСКАЯ (20)',
            'ЧУВАШСКАЯ (21)',
            'ЧУКОТСКИЙ АО (87)',
            'ЯМАЛО-НЕНЕЦКИЙ (89)',
            'ЯРОСЛАВСКАЯ ОБЛ (76)'
        ];
    }


    protected function createLinkValue(DataObject $object, $fieldId, $value = null)
    {
        /** @var ObjectTypeField $fieldType */
        $fieldType = $this->entityManager->getRepository(ObjectTypeField::class)->find($fieldId);

        if ( ! $fieldType) {
            return;
        }

        $objectValue = new ObjectLinkValue();
        $objectValue->setObjectTypeField($fieldType);

        if ($value !== null) {
            if ($value instanceof DataObject) {
                $valueObject = $value;
            } else {
                /** @var DataObject $valueObject */
                $valueObject = $this->entityManager->getRepository(DataObject::class)->findOneBy([
                    'title' => $value
                ]);
            }
        } else {
            $valueObject = null;
        }

        $objectValue->setValue($valueObject);

        $object->addValue($objectValue);
        $objectValue->setObject($object);
    }


    protected function createLiteralValue(DataObject $object, $fieldId, $value, $isDate = false)
    {
        /** @var ObjectTypeField $fieldType */
        $fieldType = $this->entityManager->getRepository(ObjectTypeField::class)->find($fieldId);

        if ( ! $fieldType) {
            return;
        }

        if ($isDate) {
            $value = (new \DateTime($value))->format('d.m.Y');
        }

        $objectValue = new ObjectLiteralValue();
        $objectValue->setObjectTypeField($fieldType);
        $objectValue->setValue($value);

        $object->addValue($objectValue);
        $objectValue->setObject($object);
    }
}