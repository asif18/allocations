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
import { FormBuilder, FormGroup, Validators, FormControl } from '@angular/forms';
import { merge, Subscription, noop, of as observableOf, fromEvent } from 'rxjs';
import { catchError, map, startWith, switchMap, debounceTime, distinctUntilChanged, finalize } from 'rxjs/operators';
import { SnackbarService, StaffService } from '../../../_services';
import { Title } from '@angular/platform-browser';
import { DefaultApiResponse, DefaultListApiParams } from '../../../_models';
import * as _ from 'lodash';

declare interface Staffs {
  id: number;
  name: string;
  username: string;
  email: string;
}

declare interface FormDependencyData {
  roles: Array<string>;
}

@Component({
  selector: 'app-staff',
  templateUrl: './staffs-list.component.html',
  styleUrls: ['./staffs-list.component.scss']
})
export class StaffsListComponent implements OnInit, AfterViewInit, OnDestroy {

  public isFormLoading: boolean = false;
  public isTableLoading: boolean = true;
  public form: FormGroup;
  private submitted = false;
  private subscriptions: Subscription[] = [];
  public displayedColumns: string[] = ['username', 'name', 'email', 'role', 'status', 'edit'];
  public data: Staffs[] = [];
  public resultsLength = 0;
  public pageSize: number = 10;
  public formDependencyData: FormDependencyData = {
    roles: ['SUPERADMIN_STAFF', 'STAFF']
  };

  @ViewChild(MatPaginator, {static: true}) paginator: MatPaginator;
  @ViewChild(MatSort, {static: true}) sort: MatSort;
  @ViewChild('searchInput', {static: true}) searchInput: ElementRef;

  constructor(
    private formBuilder: FormBuilder,
    private snackBar: SnackbarService,
    private titleService: Title,
    private staffService: StaffService) { }

  ngOnInit() {
    this.titleService.setTitle('Staffs');
    this.initForm();
  }

  ngAfterViewInit() {
    // If the user changes the sort order, reset back to the first page.
    this.sort.sortChange.subscribe(() => this.paginator.pageIndex = 0);

    fromEvent(this.searchInput.nativeElement, 'keyup')
    .pipe(
      debounceTime(150),
      distinctUntilChanged(),
      switchMap(() => {
        this.isTableLoading = true;
        this.paginator.pageIndex = 0;
        return this.getStaffs();
      }),
      map(response => {
        this.isTableLoading = false;
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
          this.isTableLoading = true;
          return this.getStaffs();
        }),
        map(response => {
          this.isTableLoading = false;
          this.resultsLength = _.size(response.data.items);
          return response.data.items;
        }),
        catchError(() => {
          return observableOf([]);
        })
      ).subscribe(data => this.data = data);
  }

  private getStaffs() {
    const params: DefaultListApiParams = {
      searchBy: this.searchInput.nativeElement.value,
      startFrom: this.paginator.pageIndex,
      endTo: this.paginator.pageIndex + this.paginator.pageSize,
      sortBy: this.sort.active,
      sortDirection: this.sort.direction,
    };
    return this.staffService.getStaffs(params);
  }

  private initForm() {
    this.form = this.formBuilder.group({
      role: new FormControl(null, {
        validators: [
          Validators.required
        ]
      }),
      name: new FormControl(null, {
        validators: [
          Validators.required,
          Validators.maxLength(35),
          Validators.minLength(3),
          Validators.pattern(/^[ a-zA-Z0-9]+$/)
        ]
      }),
      email: new FormControl(null, {
        validators: [
          Validators.required,
          Validators.email
        ],
      }),
      username: new FormControl(null, {
        validators: [
          Validators.required,
          Validators.maxLength(35),
          Validators.minLength(5),
          Validators.pattern(/^\S*$/)
        ]
      }),
      password: new FormControl(null, {
        validators: [
          Validators.required,
          Validators.maxLength(15),
          Validators.minLength(5),
          Validators.pattern(/^\S*$/)
        ]
      })
    });
  }

  resetSearchValue(): void {
    this.searchInput.nativeElement.value = '';
    const event = new KeyboardEvent('keyup', {bubbles: true});
    this.searchInput.nativeElement.dispatchEvent(event);
  }

  enableDisableStaff(id: string): void {
    this.isTableLoading = true;

    const promise = this.staffService.enableDisableStaff(btoa(id));

    this.subscriptions.push(promise.pipe(finalize(() => this.isTableLoading = false))
      .subscribe((response: DefaultApiResponse) => {
        if (response) {
          this.resetSearchValue();
          this.snackBar.show(response.message, response.status ? 'success' : 'danger');
        }
      }, () => noop()));
  }

  get f() {
    return this.form.controls;
  }

  onSubmit() {
    this.submitted = true;

    if (this.form.invalid) {
      return;
    }

    this.isFormLoading = true;

    const promise = this.staffService.saveStaff(this.form.value);

    this.subscriptions.push(promise.pipe(finalize(() => this.isFormLoading = false))
      .subscribe((response: DefaultApiResponse) => {
        if (response) {
          this.resetSearchValue();
          this.snackBar.show(response.message, response.status ? 'success' : 'danger');
        }
      }, () => noop()));
  }

  ngOnDestroy() {
    this.subscriptions.forEach(subscription => subscription.unsubscribe());
  }
}
