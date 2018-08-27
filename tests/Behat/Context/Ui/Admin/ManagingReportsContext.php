<?php

declare(strict_types=1);

namespace Tests\Odiseo\SyliusReportPlugin\Behat\Context\Ui\Admin;

use Behat\Behat\Context\Context;
use Odiseo\SyliusReportPlugin\Model\ReportInterface;
use Sylius\Behat\NotificationType;
use Sylius\Behat\Page\SymfonyPageInterface;
use Sylius\Behat\Service\NotificationCheckerInterface;
use Sylius\Behat\Service\Resolver\CurrentPageResolverInterface;
use Tests\Odiseo\SyliusReportPlugin\Behat\Page\Admin\Report\CreatePageInterface;
use Tests\Odiseo\SyliusReportPlugin\Behat\Page\Admin\Report\IndexPageInterface;
use Tests\Odiseo\SyliusReportPlugin\Behat\Page\Admin\Report\UpdatePageInterface;
use Webmozart\Assert\Assert;

final class ManagingReportsContext implements Context
{
    /** @var CurrentPageResolverInterface */
    private $currentPageResolver;

    /** @var NotificationCheckerInterface */
    private $notificationChecker;

    /** @var IndexPageInterface */
    private $indexPage;

    /** @var CreatePageInterface */
    private $createPage;

    /** @var UpdatePageInterface */
    private $updatePage;

    /**
     * @param CurrentPageResolverInterface $currentPageResolver
     * @param NotificationCheckerInterface $notificationChecker
     * @param IndexPageInterface $indexPage
     * @param CreatePageInterface $createPage
     * @param UpdatePageInterface $updatePage
     */
    public function __construct(
        CurrentPageResolverInterface $currentPageResolver,
        NotificationCheckerInterface $notificationChecker,
        IndexPageInterface $indexPage,
        CreatePageInterface $createPage,
        UpdatePageInterface $updatePage
    ) {
        $this->currentPageResolver = $currentPageResolver;
        $this->notificationChecker = $notificationChecker;
        $this->indexPage = $indexPage;
        $this->createPage = $createPage;
        $this->updatePage = $updatePage;
    }

    /**
     * @Given I want to add a new report
     * @throws \Sylius\Behat\Page\UnexpectedPageException
     */
    public function iWantToAddNewReport()
    {
        $this->createPage->open(); // This method will send request.
    }

    /**
     * @When I fill the code with :reportCode
     * @param $reportCode
     * @throws \Behat\Mink\Exception\ElementNotFoundException
     */
    public function iFillTheCodeWith($reportCode)
    {
        $this->createPage->fillCode($reportCode);
    }

    /**
     * @When I fill the name with :reportName
     * @param $reportName
     * @throws \Behat\Mink\Exception\ElementNotFoundException
     */
    public function iFillTheNameWith($reportName)
    {
        $this->createPage->fillName($reportName);
    }

    /**
     * @When I fill the description with :reportDescription
     * @param $reportDescription
     * @throws \Behat\Mink\Exception\ElementNotFoundException
     */
    public function iFillTheDescriptionWith($reportDescription)
    {
        $this->createPage->fillDescription($reportDescription);
    }

    /**
     * @When I add it
     * @throws \Behat\Mink\Exception\ElementNotFoundException
     */
    public function iAddIt()
    {
        $this->createPage->create();
    }

    /*Then I should be notified that the report has been successfully created

    public function iShouldBeNotifiedThatTheReportHasBeenSuccessfullyCreated(): void
    {
        $this->notificationChecker->checkNotification(
            'Report has been successfully created.',
            NotificationType::success()
        );
    }*/

    /**
     * @Then /^the (report "([^"]+)") should appear in the admin/
     * @param ReportInterface $report
     * @throws \Sylius\Behat\Page\UnexpectedPageException
     */
    public function reportShouldAppearInTheAdmin(ReportInterface $report) // This step use Report transformer to get Report object.
    {
        $this->indexPage->open();

        //Webmozart assert library.
        Assert::true(
            $this->indexPage->isSingleResourceOnPage(['code' => $report->getCode()]),
            sprintf('Report %s should exist but it does not', $report->getCode())
        );
    }

    /**
     * @Then I should be notified that the form contains invalid fields
     */
    public function iShouldBeNotifiedThatTheFormContainsInvalidFields(): void
    {
        Assert::true($this->resolveCurrentPage()->containsError(),
            sprintf('The form should be notified you that the form contains invalid field but it does not')
        );
    }

    /**
     * @Then I should be notified that there is already an existing report with provided code
     */
    public function iShouldBeNotifiedThatThereIsAlreadyAnExistingReportWithCode(): void
    {
        Assert::true($this->resolveCurrentPage()->containsErrorWithMessage(
            'There is an existing report with this code.',
            false
        ));
    }

    /**
     * @return IndexPageInterface|CreatePageInterface|UpdatePageInterface|SymfonyPageInterface
     */
    private function resolveCurrentPage(): SymfonyPageInterface
    {
        return $this->currentPageResolver->getCurrentPageWithForm([
            $this->indexPage,
            $this->createPage,
            $this->updatePage,
        ]);
    }
}
