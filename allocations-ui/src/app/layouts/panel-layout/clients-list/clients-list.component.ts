/**
 * Copyrights Allocations 2021. All rights reserved
 *
 * The code, text and other elements of this application/file is copyrighted
 * You may not remove any copyright or other proprietary notices contained in this file
 * The rights granted to you use this application in your organization for your
 * business/personal purpose and not to sell or modify
 *
 * Developed by: Mohamed Asif
 * Email: mohamedasif18@gmail.com
 */
import { Component, ViewChild, OnInit, AfterViewInit, ElementRef, OnDestroy } from '@angular/core';
import { MatPaginator } from '@angular/material/paginator';
import { MatSort } from '@angular/material/sort';
import { MatBottomSheet } from '@angular/material/bottom-sheet';
import { merge, Subscription, of as observableOf, fromEvent } from 'rxjs';
import { catchError, map, startWith, switchMap, debounceTime, distinctUntilChanged, tap, finalize } from 'rxjs/operators';
import { Title } from '@angular/platform-browser';
import { Router } from '@angular/router';
import { faFileExcel } from '@fortawesome/free-solid-svg-icons';
import * as _ from 'lodash';
import { ClientService, DataShareService, UtilityService } from '../../../_services';
import { DefaultListApiParams } from '../../../_models';
import { BottomSheetFileDownloadPromptComponent } from '../../../_components/bottom-sheet-file-download-prompt';

declare interface Clients {
  id: string;
  parent_id: string;
  name: string;
  business_name: string;
  username: string;
  status: string;
}

@Component({
  selector: 'app-clients-list',
  templateUrl: './clients-list.component.html',
  styleUrls: ['./clients-list.component.scss']
})
export class ClientsListComponent implements OnInit, AfterViewInit, OnDestroy  {

  public displayedColumns: string[] = ['id', 'name', 'business_name', 'username', 'instances', 'usersCount',
    'sms_gateway', 'sms_limit', 'otp_login', 'fb_login', 'status', 'datetime', 'edit'];
  public data: Clients[] = [];
  public isFormLoading: boolean = true;
  public resultsLength = 0;
  public pageSize: number = 10;
  private subscriptions: Subscription[] = [];
  public faIcons = {faFileExcel};

  @ViewChild(MatPaginator, {static: true}) paginator: MatPaginator;
  @ViewChild(MatSort, {static: true}) sort: MatSort;
  @ViewChild('searchInput', {static: true}) searchInput: ElementRef;

  constructor(
    private clientService: ClientService,
    private titleService: Title,
    private dataShareService: DataShareService,
    private bottomSheet: MatBottomSheet,
    private utilityService: UtilityService,
    private router: Router) {}

  ngOnInit(): void {
    this.titleService.setTitle('Clients List');
    this.subscriptions.push(this.dataShareService.receivedMessage.subscribe(data => {
      if (_.isNull(data)) {
        return;
      }

      const params: DefaultListApiParams =  {
        searchBy: this.searchInput.nativeElement.value,
        startFrom: this.paginator.pageIndex,
        endTo: this.paginator.pageIndex + this.paginator.pageSize,
        sortBy: this.sort.active,
        sortDirection: this.sort.direction,
      };

      switch (data) {
        case 'downloadVisibled':
          break;

        case 'downloadFiltered':
          params.startFrom = null;
          params.endTo = null;
          break;

        case 'downloadAll':
          params.searchBy = null;
          params.startFrom = null;
          params.endTo = null;
          break;
      }

      this.isFormLoading = true;
      this.clientService.exportClients(params)
        .pipe(finalize(() => this.isFormLoading = false))
        .subscribe((response) => {
          const fileName = this.utilityService.getFileNameFromHttpResponse(response);
          const options = { type: response.body.type };
          this.utilityService.downloadBlobFile(response.body, options, fileName);
        });
    }));
  }

  ngAfterViewInit() {
    // If the user changes the sort order, reset back to the first page.
    this.sort.sortChange.subscribe(() => this.paginator.pageIndex = 0);

    fromEvent(this.searchInput.nativeElement, 'keyup')
    .pipe(
      debounceTime(150),
      distinctUntilChanged(),
      switchMap(() => {
        this.isFormLoading = true;
        this.paginator.pageIndex = 0;
        return this.getClients();
      }),
      map(response => {
        this.isFormLoading = false;
        this.resultsLength = _.size(response.data.items);
        return response.data.items;
      }),
      catchError(() => {
        return observableOf([]);
      })
    ).subscribe(data => this.data = data);

    merge(this.sort.sortChange, this.paginator.page)
      .pipe(
        startWith({}),
        switchMap(() => {
          this.isFormLoading = true;
          return this.getClients();
        }),
        map(response => {
          this.isFormLoading = false;
          this.resultsLength = _.size(response.data.items);
          return response.data.items;
        }),
        catchError(() => {
          return observableOf([]);
        })
      ).subscribe(data => this.data = data);
  }

  private getClients() {
    const params: DefaultListApiParams = {
      searchBy: this.searchInput.nativeElement.value,
      startFrom: this.paginator.pageIndex,
      endTo: this.paginator.pageIndex + this.paginator.pageSize,
      sortBy: this.sort.active,
      sortDirection: this.sort.direction,
    };
    return this.clientService.getClients(params);
  }

  resetSearchValue(): void {
    this.searchInput.nativeElement.value = '';
    const event = new KeyboardEvent('keyup', {bubbles: true});
    this.searchInput.nativeElement.dispatchEvent(event);
  }

  openBottomSheet(): void {
    const items: object[] = [
      {
        action: 'downloadVisibled',
        title: 'Download What You See',
        description: 'downloads excel sheet with data that is visible in this page'
      },
      {
        action: 'downloadFiltered',
        title: 'Download Filtered',
        description: 'downloads excel sheet with data that is filtered if any'
      },
      {
        action: 'downloadAll',
        title: 'Download All',
        description: 'downloads excel sheet with entire data'
      }
    ];
    this.bottomSheet.open(BottomSheetFileDownloadPromptComponent, {
      data: {component: 'ClientsListComponent', items}
    });
  }

  navigate(id: string) {
    this.router.navigate([`panel/client/${btoa(id)}`]);
  }

  ngOnDestroy() {
    this.subscriptions.forEach(subscription => subscription.unsubscribe());
  }
}
