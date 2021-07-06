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
import { Component, OnInit, OnDestroy } from '@angular/core';
import { FormBuilder, FormGroup, Validators, FormControl } from '@angular/forms';
import { noop, Subscription } from 'rxjs';
import { finalize } from 'rxjs/operators';
import { Title } from '@angular/platform-browser';
import * as _ from 'lodash';
import { SnackbarService, AccessPointManagerService } from '../../../_services';

interface InstancetData {
  name: string;
  ipAddress: string;
  port: string;
  username: string;
  password: string;
  wifiDefaultPassword: string;
  lanIpAddress: string;
  destinationUrl: string;
}

@Component({
  selector: 'app-access-point-manager',
  templateUrl: './access-point-manager.component.html',
  styleUrls: ['./access-point-manager.component.scss']
})
export class AccessPointManagerComponent implements OnInit, OnDestroy {

  public isFormLoading: boolean = false;
  public form: FormGroup;
  private submitted = false;
  private subscriptions: Subscription[] = [];
  public cardTitle: string = 'Add New Access Point Manager';
  public profiles: object[] = [];

  constructor(
    private formBuilder: FormBuilder,
    private accessPointManagerService: AccessPointManagerService,
    private snackBar: SnackbarService,
    private titleService: Title) { }

  ngOnInit(): void {
    this.titleService.setTitle('Add New Acesss Point Manager');
    this.initForm();
  }

  private initForm() {
    this.form = this.formBuilder.group({
      host: new FormControl(null, {
        validators: [
          Validators.required,
          Validators.maxLength(50)
        ]
      }),
      comment: new FormControl(null, {
        validators: [
          Validators.required,
          Validators.maxLength(50)
        ]
      })
    });
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

    this.subscriptions.push(this.accessPointManagerService.saveAccessPointManager(this.form.value)
      .pipe(finalize(() => this.isFormLoading = false))
      .subscribe((response: any) => {
        if (response) {
          this.snackBar.show(response.message, response.status ? 'success' : 'danger');
        }
      }, () => noop()));
  }

  ngOnDestroy() {
    this.subscriptions.forEach(subscription => subscription.unsubscribe());
  }
}
